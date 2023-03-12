<?php

namespace PE\Component\WAMP\Router\Role;

use PE\Component\WAMP\Message\CallMessage;
use PE\Component\WAMP\Message\CancelMessage;
use PE\Component\WAMP\Message\ErrorMessage;
use PE\Component\WAMP\Message\InterruptMessage;
use PE\Component\WAMP\Message\InvocationMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\MessageFactory;
use PE\Component\WAMP\Message\RegisteredMessage;
use PE\Component\WAMP\Message\RegisterMessage;
use PE\Component\WAMP\Message\ResultMessage;
use PE\Component\WAMP\Message\UnregisteredMessage;
use PE\Component\WAMP\Message\UnregisterMessage;
use PE\Component\WAMP\Message\WelcomeMessage;
use PE\Component\WAMP\Message\YieldMessage;
use PE\Component\WAMP\Router\Call;
use PE\Component\WAMP\Router\Procedure;
use PE\Component\WAMP\Router\Router;
use PE\Component\WAMP\Router\RouterModuleInterface;
use PE\Component\WAMP\Router\Session\SessionInterface;
use PE\Component\WAMP\Util;
use PE\Component\WAMP\Util\EventsInterface;

final class DealerModule implements RouterModuleInterface
{
    /**
     * @var DealerFeatureInterface[]
     */
    private array $features;

    /**
     * @var Procedure[]
     */
    private array $procedures = [];

    /**
     * Calls by request id (for caller)
     *
     * @var Call[]
     */
    private array $calls = [];

    /**
     * Calls by invocation id (for callee)
     *
     * @var Call[]
     */
    private array $invocations = [];

    /**
     * Calls by interrupt id (for callee)
     *
     * @var Call[]
     */
    private array $interrupts = [];

    public function __construct(DealerFeatureInterface ...$features)
    {
        $this->features = $features;
    }

    public function attach(EventsInterface $events): void
    {
        $events->attach(Router::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
        $events->attach(Router::EVENT_MESSAGE_SEND, [$this, 'onMessageSend']);
    }

    public function detach(EventsInterface $events): void
    {
        $events->detach(Router::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
        $events->detach(Router::EVENT_MESSAGE_SEND, [$this, 'onMessageSend']);
    }

    /**
     * @param Message $message
     * @param SessionInterface $session
     */
    public function onMessageReceived(Message $message, SessionInterface $session): void
    {
        switch (true) {
            case ($message instanceof RegisterMessage):
                $this->processRegisterMessage($session, $message);
                break;
            case ($message instanceof UnregisterMessage):
                $this->processUnregisterMessage($session, $message);
                break;
            case ($message instanceof CallMessage):
                $this->processCallMessage($session, $message);
                break;
            case ($message instanceof YieldMessage):
                $this->processYieldMessage($session, $message);
                break;
            case ($message instanceof CancelMessage):
                $this->processCancelMessage($session, $message);
                break;
            case ($message instanceof ErrorMessage):
                $this->processErrorMessage($session, $message);
                break;
        }
    }

    /**
     * @param Message $message
     */
    public function onMessageSend(Message $message): void
    {
        if ($message instanceof WelcomeMessage) {
            // Possible features, by default disabled
            $message->setFeatures('dealer', [
                'payload_passthru_mode'      => false,
                'caller_identification'      => false,
                'progressive_call_results'   => false,
                'call_cancelling'            => false,
                'call_timeout'               => false,
                'call_trustlevels'           => false,
                'pattern_based_registration' => false,
                'shared_registration'        => false,
            ]);
            foreach ($this->features as $feature) {
                $message->setFeature('dealer', $feature->getName());
            }
        }
    }

    /**
     * Process REGISTER message from CALLEE
     *
     * @param SessionInterface $session
     * @param RegisterMessage $message
     */
    private function processRegisterMessage(SessionInterface $session, RegisterMessage $message): void
    {
        $registrationID = Util::generateID();

        foreach ($this->procedures as $procedure) {
            if ($message->getProcedureURI() === $procedure->getProcedureURI()) {
                $session->send(MessageFactory::createErrorMessageFromMessage($message, Message::ERROR_PROCEDURE_ALREADY_EXISTS));
                return;
            }
        }

        $this->procedures[] = new Procedure($session, $message->getProcedureURI(), $registrationID);

        $session->send(new RegisteredMessage($message->getRequestID(), $registrationID));
    }

    /**
     * Process UNREGISTER message from CALLEE
     *
     * @param SessionInterface $session
     * @param UnregisterMessage $message
     */
    private function processUnregisterMessage(SessionInterface $session, UnregisterMessage $message): void
    {
        foreach ($this->procedures as $key => $procedure) {
            if ($message->getRegistrationID() === $procedure->getRegistrationID()) {
                $session->send(new UnregisteredMessage($message->getRequestID()));
                unset($this->procedures[$key]);
                return;
            }
        }

        $session->send(MessageFactory::createErrorMessageFromMessage($message, Message::ERROR_NO_SUCH_REGISTRATION));
    }

    /**
     * Process CALL message from CALLER
     *
     * @param SessionInterface $session
     * @param CallMessage $message
     */
    private function processCallMessage(SessionInterface $session, CallMessage $message): void
    {
        foreach ($this->procedures as $procedure) {
            if ($message->getProcedureURI() === $procedure->getProcedureURI()) {
                $invocationID = Util::generateID();
                $procedure    = $this->procedures[$message->getProcedureURI()];

                // If supported call_trustlevels feature you may pass trustlevel option with integer value
                $invocation = new InvocationMessage(
                    $invocationID,
                    $procedure->getRegistrationID(),
                    [
                        'receive_progress' => (bool) $message->getOption('receive_progress'),
                    ],
                    $message->getArguments(),
                    $message->getArgumentsKw()
                );

                $call = new Call();
                $call->setCalleeSession($procedure->getSession());
                $call->setCallerSession($session);
                $call->setInvocationMessage($invocation);

                $this->calls[$message->getRequestID()] = $call;
                $this->invocations[$invocationID]      = $call;

                $session->send($invocation);
                return;
            }
        }

        $session->send(MessageFactory::createErrorMessageFromMessage($message, Message::ERROR_NO_SUCH_PROCEDURE));
    }

    /**
     * Process YIELD message from CALLEE
     *
     * @param SessionInterface $session
     * @param YieldMessage $message
     */
    private function processYieldMessage(SessionInterface $session, YieldMessage $message): void
    {
        if (isset($this->invocations[$message->getRequestID()])) {
            $call = $this->invocations[$message->getRequestID()];

            $details = [];

            if ($message->getOption('progress')) {
                $details['progress'] = true;
            } else {
                $this->removeCall($call);
            }

            $call->getCallerSession()->send(new ResultMessage(
                $call->getCallMessage()->getRequestID(),
                $details,
                $message->getArguments(),
                $message->getArgumentsKw()
            ));
        } else {
            $session->send(MessageFactory::createErrorMessageFromMessage($message, Message::ERROR_NO_SUCH_CALL));
        }
    }

    /**
     * Process CANCEL message from CALLER
     *
     * @param SessionInterface $session
     * @param CancelMessage $message
     */
    private function processCancelMessage(SessionInterface $session, CancelMessage $message): void
    {
        if (isset($this->calls[$message->getRequestID()])) {
            $call = $this->calls[$message->getRequestID()];

            if ($call->getCallerSession() !== $session) {
                // Session mismatch - do nothing
                return;
            }

            if ($call->getInterruptMessage()) {
                // Interrupt in progress - do nothing
                return;
            }

            $call->setCancelMessage($message);
            $call->setInterruptMessage($interrupt = new InterruptMessage($call->getInvocationMessage()->getRequestID(), []));

            $call->getCalleeSession()->send($interrupt);

            $this->interrupts[$interrupt->getRequestID()] = $call;

            if ($message->getOption('mode') === 'killnowait') {
                $call->getCallerSession()->send(MessageFactory::createErrorMessageFromMessage(
                    $message,
                    Message::ERROR_CANCELLED
                ));

                $this->removeCall($call);
            }
        } else {
            $session->send(MessageFactory::createErrorMessageFromMessage($message, Message::ERROR_NO_SUCH_CALL));
        }
    }

    /**
     * Process ERROR message from CALLEE
     *
     * @param SessionInterface $session
     * @param ErrorMessage $message
     */
    private function processErrorMessage(SessionInterface $session, ErrorMessage $message): void
    {
        switch ($message->getErrorMessageCode()) {
            case Message::CODE_INVOCATION:
                $this->processErrorMessageFromInvocation($session, $message);
                break;
            case Message::CODE_INTERRUPT:
                $this->processErrorMessageFromInterrupt($session, $message);
                break;
        }
    }

    /**
     * @param SessionInterface $session
     * @param ErrorMessage $message
     */
    private function processErrorMessageFromInvocation(SessionInterface $session, ErrorMessage $message): void
    {
        if (isset($this->invocations[$message->getErrorRequestID()])) {
            $call = $this->invocations[$message->getErrorRequestID()];

            if ($call->getCalleeSession() !== $session) {
                // Session mismatch - do nothing
                return;
            }

            $error = MessageFactory::createErrorMessageFromMessage($call->getCallMessage(), $message->getErrorURI());

            $error->setArguments($message->getArguments());
            $error->setArgumentsKw($message->getArgumentsKw());
            $error->setDetails($message->getDetails());

            $call->getCallerSession()->send($error);

            $this->removeCall($call);
        } else {
            $session->send(MessageFactory::createErrorMessageFromMessage($message, Message::ERROR_NO_SUCH_CALL));
        }
    }

    /**
     * @param SessionInterface $session
     * @param ErrorMessage $message
     */
    private function processErrorMessageFromInterrupt(SessionInterface $session, ErrorMessage $message): void
    {
        if (isset($this->interrupts[$message->getErrorRequestID()])) {
            $call = $this->interrupts[$message->getErrorRequestID()];

            $error = MessageFactory::createErrorMessageFromMessage($call->getCancelMessage(), $message->getErrorURI());

            $call->getCallerSession()->send($error);

            $this->removeCall($call);
        } else {
            $session->send(MessageFactory::createErrorMessageFromMessage($message, Message::ERROR_NO_SUCH_CALL));
        }
    }

    /**
     * @param Call $call
     */
    private function removeCall(Call $call): void
    {
        unset(
            $this->calls[$call->getCallMessage()->getRequestId()],
            $this->invocations[$call->getInvocationMessage()->getRequestID()]
        );

        if ($call->getInterruptMessage()) {
            unset($this->interrupts[$call->getInterruptMessage()->getRequestId()]);
        }
    }
}
