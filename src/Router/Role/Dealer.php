<?php

namespace PE\Component\WAMP\Router\Role;

use PE\Component\WAMP\ErrorURI;
use PE\Component\WAMP\Message\CallMessage;
use PE\Component\WAMP\Message\CancelMessage;
use PE\Component\WAMP\Message\ErrorMessage;
use PE\Component\WAMP\Message\InterruptMessage;
use PE\Component\WAMP\Message\InvocationMessage;
use PE\Component\WAMP\Message\MessageFactory;
use PE\Component\WAMP\Message\RegisteredMessage;
use PE\Component\WAMP\Message\RegisterMessage;
use PE\Component\WAMP\Message\ResultMessage;
use PE\Component\WAMP\Message\UnregisteredMessage;
use PE\Component\WAMP\Message\UnregisterMessage;
use PE\Component\WAMP\Message\WelcomeMessage;
use PE\Component\WAMP\Message\YieldMessage;
use PE\Component\WAMP\MessageCode;
use PE\Component\WAMP\Router\Call;
use PE\Component\WAMP\Router\Event\Events;
use PE\Component\WAMP\Router\Event\MessageEvent;
use PE\Component\WAMP\Router\Session;
use PE\Component\WAMP\Util;

class Dealer implements RoleInterface
{
    /**
     * @var array
     */
    private $procedures = [];

    /**
     * Calls by request id (for caller)
     *
     * @var Call[]
     */
    private $calls = [];

    /**
     * Calls by invocation id (for callee)
     *
     * @var Call[]
     */
    private $invocations = [];

    /**
     * Calls by interrupt id (for callee)
     *
     * @var Call[]
     */
    private $interrupts = [];

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::MESSAGE_RECEIVED => 'onMessageReceived',
            Events::MESSAGE_SEND     => 'onMessageSend',
        ];
    }

    /**
     * @param MessageEvent $event
     */
    public function onMessageReceived(MessageEvent $event)
    {
        $session = $event->getSession();
        $message = $event->getMessage();

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
     * @param MessageEvent $event
     */
    public function onMessageSend(MessageEvent $event)
    {
        $message = $event->getMessage();

        if ($message instanceof WelcomeMessage) {
            $message->addFeatures('dealer', [
                //TODO
            ]);
        }
    }

    /**
     * Process REGISTER message from CALLEE
     *
     * @param Session         $session
     * @param RegisterMessage $message
     */
    private function processRegisterMessage(Session $session, RegisterMessage $message)
    {
        $registrationID = Util::generateID();

        if (!isset($this->procedures[$message->getProcedureURI()])) {
            $this->procedures[$message->getProcedureURI()] = $registrationID;

            $session->send(new RegisteredMessage($message->getRequestID(), $registrationID));
        } else {
            $session->send(MessageFactory::createErrorMessageFromMessage($message, ErrorURI::_PROCEDURE_ALREADY_EXISTS));
        }
    }

    /**
     * Process UNREGISTER message from CALLEE
     *
     * @param Session           $session
     * @param UnregisterMessage $message
     */
    private function processUnregisterMessage(Session $session, UnregisterMessage $message)
    {
        if (in_array($message->getRegistrationID(), $this->procedures, false)) {
            $procedureURI = array_search($message->getRegistrationID(), $this->procedures, false);

            $session->send(new UnregisteredMessage($message->getRequestID()));
            unset($this->procedures[$procedureURI]);
        } else {
            $session->send(MessageFactory::createErrorMessageFromMessage($message, ErrorURI::_NO_SUCH_REGISTRATION));
        }
    }

    /**
     * Process CALL message from CALLER
     *
     * @param Session     $session
     * @param CallMessage $message
     */
    private function processCallMessage(Session $session, CallMessage $message)
    {
        if (isset($this->procedures[$message->getProcedureURI()])) {
            $invocationID   = Util::generateID();
            $registrationID = $this->procedures[$message->getProcedureURI()];

            $invocation = new InvocationMessage(
                $invocationID,
                $registrationID,
                [],
                $message->getArguments(),
                $message->getArgumentsKw()
            );

            $call = new Call();
            $call->setCallerSession($session);
            $call->setInvocationMessage($invocation);

            $this->calls[$message->getRequestID()] = $call;
            $this->invocations[$invocationID]      = $call;

            $session->send($invocation);
        } else {
            $session->send(MessageFactory::createErrorMessageFromMessage($message, ErrorURI::_NO_SUCH_PROCEDURE));
        }
    }

    /**
     * Process YIELD message from CALLEE
     *
     * @param Session      $session
     * @param YieldMessage $message
     */
    private function processYieldMessage(Session $session, YieldMessage $message)
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
            $session->send(MessageFactory::createErrorMessageFromMessage($message, ErrorURI::_NO_SUCH_CALL));
        }
    }

    /**
     * Process CANCEL message from CALLER
     *
     * @param Session       $session
     * @param CancelMessage $message
     */
    private function processCancelMessage(Session $session, CancelMessage $message)
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
                    ErrorURI::_CANCELLED
                ));

                $this->removeCall($call);
            }
        } else {
            $session->send(MessageFactory::createErrorMessageFromMessage($message, ErrorURI::_NO_SUCH_CALL));
        }
    }

    /**
     * Process ERROR message from CALLEE
     *
     * @param Session      $session
     * @param ErrorMessage $message
     */
    private function processErrorMessage(Session $session, ErrorMessage $message)
    {
        switch ($message->getErrorMessageCode()) {
            case MessageCode::_INVOCATION:
                $this->processErrorMessageFromInvocation($session, $message);
                break;
            case MessageCode::_INTERRUPT:
                $this->processErrorMessageFromInterrupt($session, $message);
                break;
        }
    }

    /**
     * @param Session      $session
     * @param ErrorMessage $message
     */
    private function processErrorMessageFromInvocation(Session $session, ErrorMessage $message)
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
            $session->send(MessageFactory::createErrorMessageFromMessage($message, ErrorURI::_NO_SUCH_CALL));
        }
    }

    /**
     * @param Session      $session
     * @param ErrorMessage $message
     */
    private function processErrorMessageFromInterrupt(Session $session, ErrorMessage $message)
    {
        if (isset($this->interrupts[$message->getErrorRequestID()])) {
            $call = $this->interrupts[$message->getErrorRequestID()];

            $error = MessageFactory::createErrorMessageFromMessage($call->getCancelMessage(), $message->getErrorURI());

            $call->getCallerSession()->send($error);

            $this->removeCall($call);
        } else {
            $session->send(MessageFactory::createErrorMessageFromMessage($message, ErrorURI::_NO_SUCH_CALL));
        }
    }

    /**
     * @param Call $call
     */
    private function removeCall(Call $call)
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