<?php

namespace PE\Component\WAMP\Client\Role;

use PE\Component\WAMP\Client\ClientModuleInterface;
use PE\Component\WAMP\Client\Event\Events;
use PE\Component\WAMP\Client\Event\MessageEvent;
use PE\Component\WAMP\Client\RegistrationCollection;
use PE\Component\WAMP\ErrorURI;
use PE\Component\WAMP\Message\ErrorMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\InterruptMessage;
use PE\Component\WAMP\Message\InvocationMessage;
use PE\Component\WAMP\Message\MessageFactory;
use PE\Component\WAMP\Message\RegisteredMessage;
use PE\Component\WAMP\Message\UnregisteredMessage;
use PE\Component\WAMP\Message\YieldMessage;
use PE\Component\WAMP\MessageCode;
use PE\Component\WAMP\Session;
use React\Promise\CancellablePromiseInterface;
use React\Promise\FulfilledPromise;
use React\Promise\PromiseInterface;

class CalleeModule implements ClientModuleInterface
{
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
     *
     * @throws \InvalidArgumentException
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
     *
     * @throws \InvalidArgumentException
     */
    private function processInvocationMessage(Session $session, InvocationMessage $message)
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
                    $result = new FulfilledPromise($result);
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

    /**
     * @param Session          $session
     * @param InterruptMessage $message
     *
     * @throws \InvalidArgumentException
     */
    private function processInterruptMessage(Session $session, InterruptMessage $message)
    {
        if (isset($session->invocationCancellers[$message->getRequestID()])) {
            $callable = $session->invocationCancellers[$message->getRequestID()];
            $callable();

            unset($session->invocationCancellers[$message->getRequestID()]);

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