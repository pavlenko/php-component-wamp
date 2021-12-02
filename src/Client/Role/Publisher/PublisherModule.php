<?php

namespace PE\Component\WAMP\Client\Role\Publisher;

use PE\Component\WAMP\Client\Client;
use PE\Component\WAMP\Client\ClientModuleInterface;
use PE\Component\WAMP\Client\Role\Publisher\Feature\FeatureInterface;
use PE\Component\WAMP\Client\Session;
use PE\Component\WAMP\Message\ErrorMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\PublishedMessage;
use React\Promise\Deferred;

final class PublisherModule implements ClientModuleInterface
{
    /**
     * @var FeatureInterface[]
     */
    private array $features = [];

    public function addFeature(FeatureInterface $feature): void
    {
        $this->features[get_class($feature)] = $feature;
    }

    public function subscribe(Client $client): void
    {
        $client->on(Client::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
        $client->on(Client::EVENT_MESSAGE_SEND, [$this, 'onMessageSend']);
    }

    public function unsubscribe(Client $client): void
    {
        $client->off(Client::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
        $client->off(Client::EVENT_MESSAGE_SEND, [$this, 'onMessageSend']);
    }

    public function onMessageReceived(Message $message, Session $session): void
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

    private function processPublishedMessage(Session $session, PublishedMessage $message): void
    {
        if (isset($session->publishRequests[$id = $message->getRequestID()])) {
            /* @var $deferred Deferred */
            $deferred = $session->publishRequests[$id];
            $deferred->resolve();

            unset($session->publishRequests[$id]);
        }
    }

    private function processErrorMessage(Session $session, ErrorMessage $message): void
    {
        if (isset($session->publishRequests[$id = $message->getErrorRequestID()])) {
            /* @var $deferred Deferred */
            $deferred = $session->publishRequests[$id];
            $deferred->resolve();

            unset($session->publishRequests[$id]);
        }
    }
}
