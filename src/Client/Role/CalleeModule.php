<?php

namespace PE\Component\WAMP\Client\Role;

use PE\Component\WAMP\Client\ClientInterface;
use PE\Component\WAMP\Client\ClientModuleInterface;
use PE\Component\WAMP\Client\Session\SessionInterface;
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
    /**
     * @var CalleeFeatureInterface[]
     */
    private array $features;

    public function __construct(CalleeFeatureInterface ...$features)
    {
        $this->features = $features;
    }

    public function attach(EventsInterface $events): void
    {
        $events->attach(ClientInterface::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
        $events->attach(ClientInterface::EVENT_MESSAGE_SEND, [$this, 'onMessageSend']);
    }

    public function detach(EventsInterface $events): void
    {
        $events->detach(ClientInterface::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
        $events->detach(ClientInterface::EVENT_MESSAGE_SEND, [$this, 'onMessageSend']);
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
            // Possible features, by default disabled
            $message->setFeatures('callee', [
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
                $message->setFeature('callee', $feature->getName());
            }
        }
    }

    private function processRegisteredMessage(SessionInterface $session, RegisteredMessage $message): void
    {
        $registrations = $session->registrations ?: [];
        foreach ($registrations as $registration) {
            if ($registration->getRegisterRequestID() === $message->getRegistrationID()) {
                $registration->setRegistrationID($message->getRegistrationID());
                $registration->getRegisterDeferred()->resolve();
            }
        }
    }

    private function processUnregisteredMessage(SessionInterface $session, UnregisteredMessage $message): void
    {
        $registrations = $session->registrations ?: [];
        foreach ($registrations as $key => $registration) {
            if ($registration->getUnregisterRequestID() === $message->getRequestID()) {
                $registration->getUnregisterDeferred()->resolve();
                unset($registrations[$key]);
            }
        }
        $session->registrations = $registrations;
    }

    private function processInvocationMessage(SessionInterface $session, InvocationMessage $message)
    {
        $session->registrations = $session->registrations ?: [];
        foreach ($session->registrations as $registration) {
            if ($registration->getRegistrationID() === $message->getRegistrationID()) {
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
                        $session->invocationCancellers = array_merge(
                            $session->invocationCancellers ?: [],
                            [$message->getRequestID() => [$result, 'cancel']]
                        );

                        // @codeCoverageIgnoreStart
                        $result = $result->then(function ($result) use ($session, $message) {
                            // Process via local var for prevent indirect modification error
                            $cancellers = $session->invocationCancellers ?: [];
                            unset($cancellers[$message->getRequestID()]);
                            $session->invocationCancellers = $cancellers;
                            return $result;
                        });
                        // @codeCoverageIgnoreEnd
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
    }

    private function processInterruptMessage(SessionInterface $session, InterruptMessage $message): void
    {
        $cancellers = $session->invocationCancellers ?: [];
        if (isset($cancellers[$message->getRequestID()])) {
            $callable = $cancellers[$message->getRequestID()];
            $callable();

            unset($cancellers[$message->getRequestID()]);

            $session->send(MessageFactory::createErrorMessageFromMessage($message, Message::ERROR_CANCELLED));
        }
        $session->invocationCancellers = $cancellers;
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
        $registrations = $session->registrations ?: [];
        foreach ($registrations as $key => $registration) {
            if ($registration->getRegisterRequestID() === $message->getErrorRequestID()) {
                $registration->getRegisterDeferred()->reject();
                unset($registrations[$key]);
            }
        }
        $session->registrations = $registrations;
    }

    private function processErrorMessageFromUnregister(SessionInterface $session, ErrorMessage $message): void
    {
        $registrations = $session->registrations ?: [];
        foreach ($registrations as $key => $registration) {
            if ($registration->getUnregisterRequestID() === $message->getErrorRequestID()) {
                $registration->getUnregisterDeferred()->reject();
                unset($registrations[$key]);
            }
        }
        $session->registrations = $registrations;
    }
}