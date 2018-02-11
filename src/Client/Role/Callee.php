<?php

namespace PE\Component\WAMP\Client\Role;

use PE\Component\WAMP\Client\Event\Events;
use PE\Component\WAMP\Client\Event\MessageEvent;
use PE\Component\WAMP\Client\InvocationResult;
use PE\Component\WAMP\Client\Registration;
use PE\Component\WAMP\Client\RegistrationCollection;
use PE\Component\WAMP\ErrorURI;
use PE\Component\WAMP\Message\ErrorMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\InterruptMessage;
use PE\Component\WAMP\Message\InvocationMessage;
use PE\Component\WAMP\Message\MessageFactory;
use PE\Component\WAMP\Message\RegisteredMessage;
use PE\Component\WAMP\Message\RegisterMessage;
use PE\Component\WAMP\Message\UnregisteredMessage;
use PE\Component\WAMP\Message\UnregisterMessage;
use PE\Component\WAMP\Message\YieldMessage;
use PE\Component\WAMP\MessageCode;
use PE\Component\WAMP\Session;
use PE\Component\WAMP\Util;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use React\Promise\RejectedPromise;

class Callee implements RoleInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @deprecated
     * @var callable[]
     */
    private $cancellers = [];

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

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
            case ($message instanceof RegisteredMessage):
                $this->processRegisteredMessage($session, $message);
                break;
            case ($message instanceof UnregisteredMessage):
                $this->processUnregisteredMessage($session, $message);
                break;
            case ($message instanceof InvocationMessage):
                $this->processInvocationMessage($session, $message);
                break;
            case ($message instanceof InterruptMessage):
                $this->processInterruptMessage($session, $message);
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

        if ($message instanceof HelloMessage) {
            $message->addFeatures('callee', [
                //TODO
            ]);
        }
    }

    /**
     * @param string   $procedureURI
     * @param callable $callback
     * @param array    $options
     *
     * @return PromiseInterface
     *
     * @throws \InvalidArgumentException
     */
    public function register($procedureURI, callable $callback, array $options = [])
    {
        if (!($this->session->registrations instanceof RegistrationCollection)) {
            $this->session->registrations = new RegistrationCollection();
        }

        if ($this->session->registrations->findByProcedureURI($procedureURI)) {
            throw new \InvalidArgumentException(sprintf('Procedure with uri "%s" already registered', $procedureURI));
        }

        $requestId = Util::generateID();

        $registration = new Registration($procedureURI, $callback);
        $registration->setRegisterRequestID($requestId);
        $registration->setRegisterDeferred($deferred = new Deferred());

        $this->session->registrations[$procedureURI] = $registration;

        $this->session->send(new RegisterMessage($requestId, $options, $procedureURI));

        return $deferred->promise();
    }

    /**
     * @param string $procedureURI
     *
     * @return PromiseInterface
     *
     * @throws \InvalidArgumentException
     */
    public function unregister($procedureURI)
    {
        $requestID     = Util::generateID();
        $registrations = $this->session->registrations ?: new RegistrationCollection();

        if ($registration = $registrations->findByProcedureURI($procedureURI)) {
            $registration->getRegisterDeferred()->reject();

            $registration->setCallback(null);
            $registration->setUnregisterRequestID($requestID);
            $registration->setUnregisterDeferred($deferred = new Deferred());

            $this->session->send(new UnregisterMessage($requestID, $registration->getRegistrationID()));

            return $deferred->promise();
        }

        return new RejectedPromise();
    }

    /**
     * @param Session           $session
     * @param RegisteredMessage $message
     */
    private function processRegisteredMessage(Session $session, RegisteredMessage $message)
    {
        $registrations = $session->registrations ?: new RegistrationCollection();

        if ($registration = $registrations->findByRegisterRequestID($message->getRequestID())) {
            $registration->setRegistrationID($message->getRegistrationID());

            $deferred = $registration->getRegisterDeferred();
            $deferred->resolve();
        }
    }

    /**
     * @param Session             $session
     * @param UnregisteredMessage $message
     */
    private function processUnregisteredMessage(Session $session, UnregisteredMessage $message)
    {
        $registrations = $session->registrations ?: new RegistrationCollection();

        if ($registration = $registrations->findByUnregisterRequestID($message->getRequestID())) {
            $deferred = $registration->getUnregisterDeferred();
            $deferred->resolve();

            $registrations->remove($registration);
        }
    }

    /**
     * @param Session           $session
     * @param InvocationMessage $message
     */
    private function processInvocationMessage(Session $session, InvocationMessage $message)
    {
        //TODO update logic
        $registrations = $session->registrations ?: new RegistrationCollection();

        if ($registration = $registrations->findByRegistrationID($message->getRegistrationID())) {
            if ($registration->getCallback() === null) {
                // Callback can be empty if unregister request occurred, but not completed
                $session->send(MessageFactory::createErrorMessageFromMessage($message));
                return;
            }

            try {
                $yield = new YieldMessage($message->getRequestID(), []);

                $result = call_user_func(
                    $registration->getCallback(),
                    $message->getArguments(),
                    $message->getArgumentsKw(),
                    $message->getDetails()
                );

                if ($result instanceof InvocationResult) {
                    if ($canceller = $result->getCanceller()) {
                        $this->cancellers[$message->getRequestID()] = $canceller;
                    }

                    $yield->setArguments($result->getArguments());
                    $yield->setArgumentsKw($result->getArgumentsKw());
                }

                $session->send($yield);
            } catch (\Exception $exception) {
                $error = MessageFactory::createErrorMessageFromMessage($message);
                $error->setArguments([$exception->getMessage()]);
                $error->setArgumentsKw([$exception]);

                $session->send($error);
            }
        }
    }

    /**
     * @param Session          $session
     * @param InterruptMessage $message
     */
    private function processInterruptMessage(Session $session, InterruptMessage $message)
    {
        if (isset($this->cancellers[$message->getRequestID()])) {
            $callable = $this->cancellers[$message->getRequestID()];
            $callable();

            unset($this->cancellers[$message->getRequestID()]);

            $session->send(MessageFactory::createErrorMessageFromMessage($message, ErrorURI::_CANCELLED));
        }
    }

    /**
     * @param Session      $session
     * @param ErrorMessage $message
     */
    private function processErrorMessage(Session $session, ErrorMessage $message)
    {
        switch ($message->getErrorMessageCode()) {
            case MessageCode::_REGISTER:
                $this->processErrorMessageFromRegister($session, $message);
                break;
            case MessageCode::_UNREGISTER:
                $this->processErrorMessageFromUnregister($session, $message);
                break;
        }
    }

    /**
     * @param Session      $session
     * @param ErrorMessage $message
     */
    private function processErrorMessageFromRegister(Session $session, ErrorMessage $message)
    {
        $registrations = $session->registrations ?: new RegistrationCollection();

        if ($registration = $registrations->findByRegisterRequestID($message->getErrorRequestID())) {
            $deferred = $registration->getRegisterDeferred();
            $deferred->reject();

            $registrations->remove($registration);
        }
    }

    /**
     * @param Session      $session
     * @param ErrorMessage $message
     */
    private function processErrorMessageFromUnregister(Session $session, ErrorMessage $message)
    {
        $registrations = $session->registrations ?: new RegistrationCollection();

        if ($registration = $registrations->findByUnregisterRequestID($message->getErrorRequestID())) {
            $deferred = $registration->getUnregisterDeferred();
            $deferred->reject();

            $registrations->remove($registration);
        }
    }
}