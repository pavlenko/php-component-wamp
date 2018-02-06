<?php

namespace PE\Component\WAMP\Client\Role;

use PE\Component\WAMP\Client\Event\Events;
use PE\Component\WAMP\Client\Event\MessageEvent;
use PE\Component\WAMP\Client\InvocationResult;
use PE\Component\WAMP\Client\Registration;
use PE\Component\WAMP\Client\Session;
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
use PE\Component\WAMP\Util;

class Callee implements RoleInterface
{
    /**
     * @var Registration[]
     */
    private $registrations = [];

    /**
     * @var callable[]
     */
    private $cancellers = [];

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
                $this->processRegisteredMessage($message);
                break;
            case ($message instanceof UnregisteredMessage):
                $this->processUnregisteredMessage($message);
                break;
            case ($message instanceof InvocationMessage):
                $this->processInvocationMessage($session, $message);
                break;
            case ($message instanceof InterruptMessage):
                $this->processInterruptMessage($session, $message);
                break;
            case ($message instanceof ErrorMessage):
                $this->processErrorMessage($message);
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
     * @param Session    $session
     * @param string     $procedureURI
     * @param callable   $callback
     * @param array|null $options
     */
    public function register(Session $session, $procedureURI, callable $callback, array $options = null)
    {
        if (!isset($this->registrations[$procedureURI])) {
            $requestId = Util::generateID();

            $registration = new Registration($procedureURI, $callback);
            $registration->setRegisterRequestID($requestId);

            $this->registrations[$procedureURI] = $registration;

            $session->send(new RegisterMessage($requestId, $options ?: [], $procedureURI));
        }
    }

    /**
     * @param Session $session
     * @param string  $procedureURI
     */
    public function unregister(Session $session, $procedureURI)
    {
        if (isset($this->registrations[$procedureURI])) {
            $requestID = Util::generateID();

            $registration = $this->registrations[$procedureURI];
            $registration->setUnregisterRequestID($requestID);
            $registration->setCallback(null);

            $session->send(new UnregisterMessage($requestID, $this->registrations[$procedureURI]->getRegistrationID()));
        }
    }

    /**
     * @param RegisteredMessage $message
     */
    private function processRegisteredMessage(RegisteredMessage $message)
    {
        foreach ($this->registrations as $key => $registration) {
            if ($registration->getRegisterRequestID() === $message->getRequestID()) {
                $registration->setRegistrationID($message->getRegistrationID());
                break;
            }
        }
    }

    /**
     * @param UnregisteredMessage $message
     */
    private function processUnregisteredMessage(UnregisteredMessage $message)
    {
        foreach ($this->registrations as $key => $registration) {
            if ($registration->getUnregisterRequestID() === $message->getRequestID()) {
                unset($this->registrations[$key]);
                return;
            }
        }
    }

    /**
     * @param Session           $session
     * @param InvocationMessage $message
     */
    private function processInvocationMessage(Session $session, InvocationMessage $message)
    {
        foreach ($this->registrations as $key => $registration) {
            if ($registration->getRegistrationID() === $message->getRegistrationID()) {
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
                    $error->setArgumentsKw($exception);

                    $session->send($error);
                }

                return;
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
     * @param ErrorMessage $message
     */
    private function processErrorMessage(ErrorMessage $message)
    {
        switch ($message->getErrorMessageCode()) {
            case MessageCode::_REGISTER:
                $this->processErrorMessageFromRegister($message);
                break;
            case MessageCode::_UNREGISTER:
                $this->processErrorMessageFromUnregister($message);
                break;
        }
    }

    /**
     * @param ErrorMessage $message
     */
    private function processErrorMessageFromRegister(ErrorMessage $message)
    {
        foreach ($this->registrations as $key => $registration) {
            if ($registration->getRegisterRequestID() === $message->getErrorRequestID()) {
                unset($this->registrations[$key]);
                return;
            }
        }
    }

    /**
     * @param ErrorMessage $message
     */
    private function processErrorMessageFromUnregister(ErrorMessage $message)
    {
        foreach ($this->registrations as $key => $registration) {
            if ($registration->getUnregisterRequestID() === $message->getErrorRequestID()) {
                unset($this->registrations[$key]);
                return;
            }
        }
    }
}