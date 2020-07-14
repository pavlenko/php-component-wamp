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

class PublisherModule implements ClientModuleInterface
{
    /**
     * @var FeatureInterface[]
     */
    private $features = [];

    /**
     * @param FeatureInterface $feature
     */
    public function addFeature(FeatureInterface $feature)
    {
        $this->features[get_class($feature)] = $feature;
    }

    /**
     * @inheritDoc
     */
    public function subscribe(Client $client)
    {
        $client->on(Client::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
        $client->on(Client::EVENT_MESSAGE_SEND, [$this, 'onMessageSend']);
    }

    /**
     * @inheritDoc
     */
    public function unsubscribe(Client $client)
    {
        $client->off(Client::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
        $client->off(Client::EVENT_MESSAGE_SEND, [$this, 'onMessageSend']);
    }

    /**
     * @param Message $message
     * @param Session $session
     */
    public function onMessageReceived(Message $message, Session $session)
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

    /**
     * @param Message $message
     */
    public function onMessageSend(Message $message)
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

    /**
     * @param Session          $session
     * @param PublishedMessage $message
     */
    private function processPublishedMessage(Session $session, PublishedMessage $message)
    {
        if (isset($session->publishRequests[$id = $message->getRequestID()])) {
            /* @var $deferred Deferred */
            $deferred = $session->publishRequests[$id];
            $deferred->resolve();

            unset($session->publishRequests[$id]);
        }
    }

    /**
     * @param Session      $session
     * @param ErrorMessage $message
     */
    private function processErrorMessage(Session $session, ErrorMessage $message)
    {
        if (isset($session->publishRequests[$id = $message->getErrorRequestID()])) {
            /* @var $deferred Deferred */
            $deferred = $session->publishRequests[$id];
            $deferred->resolve();

            unset($session->publishRequests[$id]);
        }
    }
}
