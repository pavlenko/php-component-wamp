<?php

namespace PE\Component\WAMP\Client\Role;

use PE\Component\WAMP\Client\Client;
use PE\Component\WAMP\Client\ClientModuleInterface;
use PE\Component\WAMP\Client\RegistrationCollection;
use PE\Component\WAMP\Client\Session;
use PE\Component\WAMP\Client\SessionInterface;
use PE\Component\WAMP\Message\ErrorMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\InterruptMessage;
use PE\Component\WAMP\Message\InvocationMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\MessageFactory;
use PE\Component\WAMP\Message\RegisteredMessage;
use PE\Component\WAMP\Message\UnregisteredMessage;
use PE\Component\WAMP\Message\YieldMessage;
use PE\Component\WAMP\Util\EventsInterface;
use React\Promise\CancellablePromiseInterface;
use React\Promise\PromiseInterface;
use function React\Promise\resolve;

final class CalleeModule implements ClientModuleInterface
{
    public function attach(EventsInterface $events): void
    {
        $events->attach(Client::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
        $events->attach(Client::EVENT_MESSAGE_SEND, [$this, 'onMessageSend']);
    }

    public function detach(EventsInterface $events): void
    {
        $events->detach(Client::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
        $events->detach(Client::EVENT_MESSAGE_SEND, [$this, 'onMessageSend']);
    }

    public function onMessageReceived(Message $message, SessionInterface $session): void
    {
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

     public function onMessageSend(Message $message): void
    {
        if ($message instanceof HelloMessage) {
            $message->addFeatures('callee', [
                //TODO
            ]);
        }
    }

    private function processRegisteredMessage(SessionInterface $session, RegisteredMessage $message): void
    {
        $registrations = $session->registrations ?: new RegistrationCollection();

        if ($registration = $registrations->findByRegisterRequestID($message->getRequestID())) {
            $registration->setRegistrationID($message->getRegistrationID());

            $deferred = $registration->getRegisterDeferred();
            $deferred->resolve();
        }
    }

    private function processUnregisteredMessage(SessionInterface $session, UnregisteredMessage $message): void
    {
        $registrations = $session->registrations ?: new RegistrationCollection();

        if ($registration = $registrations->findByUnregisterRequestID($message->getRequestID())) {
            $deferred = $registration->getUnregisterDeferred();
            $deferred->resolve();

            $registrations->remove($registration);
        }
    }

    private function processInvocationMessage(SessionInterface $session, InvocationMessage $message)
    {
        $registrations = $session->registrations ?: new RegistrationCollection();

        if ($registration = $registrations->findByRegistrationID($message->getRegistrationID())) {
            if ($registration->getCallback() === null) {
                // Callback can be empty if unregister request occurred, but not completed
                $session->send(MessageFactory::createErrorMessageFromMessage($message));
                return;
            }

            try {
                $result = call_user_func(
                    $registration->getCallback(),
                    $message->getArguments(),
                    $message->getArgumentsKw(),
                    $message->getDetails()
                );

                if (!($result instanceof PromiseInterface)) {
                    // If result is not a promise - wrap it into fulfilled promise
                    $result = resolve($result);
                }

                // Check if promise is cancellable and add canceller to session if true
                if ($result instanceof CancellablePromiseInterface) {
                    if (!is_array($session->invocationCancellers)) {
                        $session->invocationCancellers = [];
                    }

                    $session->invocationCancellers[$message->getRequestID()] = [$result, 'cancel'];

                    $result = $result->then(function ($result) use ($session, $message) {
                        unset($session->invocationCancellers[$message->getRequestID()]);
                        return $result;
                    });
                }

                // Send messages depends on invocation state
                $result->then(
                    function ($result) use ($session, $message) {
                        // Send invocation success
                        $session->send(new YieldMessage($message->getRequestID(), [], [$result]));
                    },
                    function ($error) use ($session, $message) {
                        // Send invocation error
                        $errorMessage = MessageFactory::createErrorMessageFromMessage($message);

                        if ($error instanceof \Exception) {
                            $errorMessage->setArguments([$error->getMessage()]);
                            $errorMessage->setArgumentsKw([$error]);
                        }

                        $session->send($errorMessage);
                    },
                    function ($result) use ($session, $message) {
                        // Send invocation progress
                        $session->send(new YieldMessage($message->getRequestID(), ['progress' => true], [$result]));
                    }
                );
            } catch (\Exception $exception) {
                $error = MessageFactory::createErrorMessageFromMessage($message);
                $error->setArguments([$exception->getMessage()]);
                $error->setArgumentsKw([$exception]);

                $session->send($error);
            }
        }
    }

    private function processInterruptMessage(SessionInterface $session, InterruptMessage $message): void
    {
        if (isset($session->invocationCancellers[$message->getRequestID()])) {
            $callable = $session->invocationCancellers[$message->getRequestID()];
            $callable();

            unset($session->invocationCancellers[$message->getRequestID()]);

            $session->send(MessageFactory::createErrorMessageFromMessage($message, Message::ERROR_CANCELLED));
        }
    }

    private function processErrorMessage(SessionInterface $session, ErrorMessage $message): void
    {
        switch ($message->getErrorMessageCode()) {
            case Message::CODE_REGISTER:
                $this->processErrorMessageFromRegister($session, $message);
                break;
            case Message::CODE_UNREGISTER:
                $this->processErrorMessageFromUnregister($session, $message);
                break;
        }
    }

    private function processErrorMessageFromRegister(SessionInterface $session, ErrorMessage $message): void
    {
        $registrations = $session->registrations ?: new RegistrationCollection();

        if ($registration = $registrations->findByRegisterRequestID($message->getErrorRequestID())) {
            $deferred = $registration->getRegisterDeferred();
            $deferred->reject();

            $registrations->remove($registration);
        }
    }

    private function processErrorMessageFromUnregister(SessionInterface $session, ErrorMessage $message): void
    {
        $registrations = $session->registrations ?: new RegistrationCollection();

        if ($registration = $registrations->findByUnregisterRequestID($message->getErrorRequestID())) {
            $deferred = $registration->getUnregisterDeferred();
            $deferred->reject();

            $registrations->remove($registration);
        }
    }
}