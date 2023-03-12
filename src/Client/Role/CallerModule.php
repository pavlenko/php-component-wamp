<?php

namespace PE\Component\WAMP\Client\Role;

use PE\Component\WAMP\Client\ClientInterface;
use PE\Component\WAMP\Client\ClientModuleInterface;
use PE\Component\WAMP\Client\Session\SessionInterface;
use PE\Component\WAMP\Message\ErrorMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\ResultMessage;
use PE\Component\WAMP\Util\EventsInterface;

final class CallerModule implements ClientModuleInterface
{
    /**
     * @var CallerFeatureInterface[]
     */
    private array $features;

    public function __construct(CallerFeatureInterface ...$features)
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
            case ($message instanceof ResultMessage):
                $this->processResultMessage($session, $message);
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
            $message->setFeatures('caller', [
                'payload_passthru_mode'    => false,
                'caller_identification'    => false,
                'call_cancelling'          => false,
                'progressive_call_results' => false,
            ]);
            foreach ($this->features as $feature) {
                $message->setFeature('caller', $feature->getName());
            }
        }
    }

    private function processResultMessage(SessionInterface $session, ResultMessage $message): void
    {
        $requests = $session->callRequests ?: [];
        foreach ($requests as $key => $call) {
            if ($call->getRequestID() === $message->getRequestID()) {
                if (empty($message->getDetail('progress'))) {
                    $call->getDeferred()->resolve();
                    unset($requests[$key]);
                } else {
                    $call->getDeferred()->notify();
                }
            }
        }
        $session->callRequests = $requests;
    }

    private function processErrorMessage(SessionInterface $session, ErrorMessage $message): void
    {
        if (Message::CODE_CALL === $message->getErrorMessageCode()) {
            $requests = $session->callRequests ?: [];
            foreach ($requests as $key => $call) {
                if ($call->getRequestID() === $message->getErrorRequestID()) {
                    $call->getDeferred()->reject();
                    unset($requests[$key]);
                }
            }
            $session->callRequests = $requests;
        }
    }
}