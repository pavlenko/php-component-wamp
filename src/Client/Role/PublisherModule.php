<?php

namespace PE\Component\WAMP\Client\Role;

use PE\Component\WAMP\Client\Client;
use PE\Component\WAMP\Client\ClientModuleInterface;
use PE\Component\WAMP\Client\Role\Publisher\Feature\FeatureInterface;
use PE\Component\WAMP\Client\SessionInterface;
use PE\Component\WAMP\Message\ErrorMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\PublishedMessage;
use PE\Component\WAMP\Util\EventsInterface;

final class PublisherModule implements ClientModuleInterface
{
    /**
     * @var FeatureInterface[]
     */
    private array $features;

    public function __construct(FeatureInterface ...$features)
    {
        $this->features = $features;
    }

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
            $features = [];
            foreach ($this->features as $feature) {
                $features[$feature->getName()] = true;
            }

            $message->addFeatures('publisher', $features);
        } else {
            foreach ($this->features as $feature) {
                $feature->onMessageSend($message);
            }
        }
    }

    private function processPublishedMessage(SessionInterface $session, PublishedMessage $message): void
    {
        $requestID = $message->getRequestID();
        if (isset($session->publishRequests[$requestID])) {
            $session->publishRequests[$requestID]->resolve();
            unset($session->publishRequests[$requestID]);
        }
    }

    private function processErrorMessage(SessionInterface $session, ErrorMessage $message): void
    {
        $requestID = $message->getErrorRequestID();
        if (isset($session->publishRequests[$requestID])) {
            $session->publishRequests[$requestID]->reject();
            unset($session->publishRequests[$requestID]);
        }
    }
}
