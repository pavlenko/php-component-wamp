<?php

namespace PE\Component\WAMP\Client\Role;

use PE\Component\WAMP\Client\Client;
use PE\Component\WAMP\Client\ClientModuleInterface;
use PE\Component\WAMP\Client\Session;
use PE\Component\WAMP\Client\SubscriptionCollection;
use PE\Component\WAMP\Message\ErrorMessage;
use PE\Component\WAMP\Message\EventMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\SubscribedMessage;
use PE\Component\WAMP\Message\UnsubscribedMessage;
use PE\Component\WAMP\MessageCode;

class SubscriberModule implements ClientModuleInterface
{
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
            case ($message instanceof SubscribedMessage):
                $this->processSubscribedMessage($session, $message);
                break;
            case ($message instanceof UnsubscribedMessage):
                $this->processUnsubscribedMessage($session, $message);
                break;
            case ($message instanceof EventMessage):
                $this->processEventMessage($session, $message);
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
            $message->addFeatures('subscriber', [
                //TODO
            ]);
        }
    }

    /**
     * @param Session           $session
     * @param SubscribedMessage $message
     */
    private function processSubscribedMessage(Session $session, SubscribedMessage $message)
    {
        $subscriptions = $session->subscriptions ?: new SubscriptionCollection();

        if ($subscription = $subscriptions->findBySubscribeRequestID($message->getRequestID())) {
            $subscription->setSubscriptionID($message->getSubscriptionID());

            $deferred = $subscription->getSubscribeDeferred();
            $deferred->resolve();
        }
    }

    /**
     * @param Session             $session
     * @param UnsubscribedMessage $message
     */
    private function processUnsubscribedMessage(Session $session, UnsubscribedMessage $message)
    {
        $subscriptions = $session->subscriptions ?: new SubscriptionCollection();

        if ($subscription = $subscriptions->findByUnsubscribeRequestID($message->getRequestID())) {
            $deferred = $subscription->getUnsubscribeDeferred();
            $deferred->resolve();

            $subscriptions->remove($subscription);
        }
    }

    /**
     * @param Session      $session
     * @param EventMessage $message
     */
    private function processEventMessage(Session $session, EventMessage $message)
    {
        $subscriptions = $session->subscriptions ?: new SubscriptionCollection();

        if ($subscription = $subscriptions->findBySubscriptionID($message->getSubscriptionID())) {
            call_user_func(
                $subscription->getCallback(),
                $message->getArguments(),
                $message->getArgumentsKw(),
                $message->getDetails(),
                $message->getPublicationID()
            );
        }
    }

    /**
     * @param Session      $session
     * @param ErrorMessage $message
     */
    private function processErrorMessage(Session $session, ErrorMessage $message)
    {
        switch ($message->getErrorMessageCode()) {
            case MessageCode::_SUBSCRIBE:
                $this->processErrorMessageFromSubscribe($session, $message);
                break;
            case MessageCode::_UNSUBSCRIBE:
                $this->processErrorMessageFromUnsubscribe($session, $message);
                break;
        }
    }

    /**
     * @param Session      $session
     * @param ErrorMessage $message
     */
    private function processErrorMessageFromSubscribe(Session $session, ErrorMessage $message)
    {
        $subscriptions = $session->subscriptions ?: new SubscriptionCollection();

        if ($subscription = $subscriptions->findBySubscribeRequestID($message->getErrorRequestID())) {
            $deferred = $subscription->getSubscribeDeferred();
            $deferred->reject();

            $subscriptions->remove($subscription);
        }
    }

    /**
     * @param Session      $session
     * @param ErrorMessage $message
     */
    private function processErrorMessageFromUnsubscribe(Session $session, ErrorMessage $message)
    {
        $subscriptions = $session->subscriptions ?: new SubscriptionCollection();

        if ($subscription = $subscriptions->findByUnsubscribeRequestID($message->getErrorRequestID())) {
            $deferred = $subscription->getUnsubscribeDeferred();
            $deferred->reject();

            $subscriptions->remove($subscription);
        }
    }
}