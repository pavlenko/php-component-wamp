<?php

namespace PE\Component\WAMP\Client\Role;

use PE\Component\WAMP\Client\ClientInterface;
use PE\Component\WAMP\Client\ClientModuleInterface;
use PE\Component\WAMP\Client\Session\SessionInterface;
use PE\Component\WAMP\Message\ErrorMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\PublishedMessage;
use PE\Component\WAMP\Util\EventsInterface;

final class PublisherModule implements ClientModuleInterface
{
    /**
     * @var PublisherFeatureInterface[]
     */
    private array $features;

    public function __construct(PublisherFeatureInterface ...$features)
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
            case ($message instanceof PublishedMessage):
                $this->processPublishedMessage($session, $message);
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
            $message->setFeatures('publisher', [
                'payload_passthru_mode'         => false,
                'publisher_exclusion'           => false,
                'publisher_identification'      => false,
                'subscriber_blackwhite_listing' => false,
            ]);
            foreach ($this->features as $feature) {
                $message->setFeature('publisher', $feature->getName());
            }
        } else {
//            foreach ($this->features as $feature) {
//                $feature->onMessageSend($message);
//            }
        }
    }

    private function processPublishedMessage(SessionInterface $session, PublishedMessage $message): void
    {
        $requests  = $session->publishRequests ?: [];
        $requestID = $message->getRequestID();
        if (isset($requests[$requestID])) {
            $requests[$requestID]->resolve();
            unset($requests[$requestID]);
        }
        $session->publishRequests = $requests;
    }

    private function processErrorMessage(SessionInterface $session, ErrorMessage $message): void
    {
        $requests  = $session->publishRequests ?: [];
        $requestID = $message->getErrorRequestID();
        if (isset($requests[$requestID])) {
            $requests[$requestID]->reject();
            unset($requests[$requestID]);
        }
        $session->publishRequests = $requests;
    }
}
