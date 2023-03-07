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

final class SubscriberModule implements ClientModuleInterface
{
    public function attach(Client $client): void
    {
        $client->on(Client::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
        $client->on(Client::EVENT_MESSAGE_SEND, [$this, 'onMessageSend']);
    }

    public function detach(Client $client): void
    {
        $client->off(Client::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
        $client->off(Client::EVENT_MESSAGE_SEND, [$this, 'onMessageSend']);
    }

    public function onMessageReceived(Message $message, Session $session): void
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

    public function onMessageSend(Message $message): void
    {
        if ($message instanceof HelloMessage) {
            $message->addFeatures('subscriber', [
                //TODO
            ]);
        }
    }

    private function processSubscribedMessage(Session $session, SubscribedMessage $message): void
    {
        $subscriptions = $session->subscriptions ?: new SubscriptionCollection();

        if ($subscription = $subscriptions->findBySubscribeRequestID($message->getRequestID())) {
            $subscription->setSubscriptionID($message->getSubscriptionID());

            $deferred = $subscription->getSubscribeDeferred();
            $deferred->resolve();
        }
    }

    private function processUnsubscribedMessage(Session $session, UnsubscribedMessage $message): void
    {
        $subscriptions = $session->subscriptions ?: new SubscriptionCollection();

        if ($subscription = $subscriptions->findByUnsubscribeRequestID($message->getRequestID())) {
            $deferred = $subscription->getUnsubscribeDeferred();
            $deferred->resolve();

            $subscriptions->remove($subscription);
        }
    }

    private function processEventMessage(Session $session, EventMessage $message): void
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

    private function processErrorMessage(Session $session, ErrorMessage $message): void
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

    private function processErrorMessageFromSubscribe(Session $session, ErrorMessage $message): void
    {
        $subscriptions = $session->subscriptions ?: new SubscriptionCollection();

        if ($subscription = $subscriptions->findBySubscribeRequestID($message->getErrorRequestID())) {
            $deferred = $subscription->getSubscribeDeferred();
            $deferred->reject();

            $subscriptions->remove($subscription);
        }
    }

    private function processErrorMessageFromUnsubscribe(Session $session, ErrorMessage $message): void
    {
        $subscriptions = $session->subscriptions ?: new SubscriptionCollection();

        if ($subscription = $subscriptions->findByUnsubscribeRequestID($message->getErrorRequestID())) {
            $deferred = $subscription->getUnsubscribeDeferred();
            $deferred->reject();

            $subscriptions->remove($subscription);
        }
    }
}