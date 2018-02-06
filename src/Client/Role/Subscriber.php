<?php

namespace PE\Component\WAMP\Client\Role;

use PE\Component\WAMP\Client\Event\Events;
use PE\Component\WAMP\Client\Event\MessageEvent;
use PE\Component\WAMP\Client\Session;
use PE\Component\WAMP\Client\Subscription;
use PE\Component\WAMP\Message\ErrorMessage;
use PE\Component\WAMP\Message\EventMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\SubscribedMessage;
use PE\Component\WAMP\Message\SubscribeMessage;
use PE\Component\WAMP\Message\UnsubscribedMessage;
use PE\Component\WAMP\Message\UnsubscribeMessage;
use PE\Component\WAMP\MessageCode;
use PE\Component\WAMP\Util;

class Subscriber implements RoleInterface
{
    /**
     * @var Subscription[]
     */
    private $subscriptions = [];

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
     */
    public function onMessageReceived(MessageEvent $event)
    {
        $message = $event->getMessage();

        switch (true) {
            case ($message instanceof SubscribedMessage):
                $this->processSubscribedMessage($message);
                break;
            case ($message instanceof UnsubscribedMessage):
                $this->processUnsubscribedMessage($message);
                break;
            case ($message instanceof EventMessage):
                $this->processEventMessage($message);
                break;
            case ($message instanceof ErrorMessage):
                $this->processErrorMessage($message);
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
            $message->addFeatures('subscriber', [
                //TODO
            ]);
        }
    }

    /**
     * @param Session    $session
     * @param string     $topic
     * @param callable   $callback
     * @param array|null $options
     */
    public function subscribe(Session $session, $topic, callable $callback, array $options = null)
    {
        $requestID = Util::generateID();
        $options   = $options ?: [];

        $subscription = new Subscription($topic, $callback);
        $subscription->setSubscribeRequestID($requestID);

        $this->subscriptions[] = $subscription;

        $session->send(new SubscribeMessage($requestID, $options, $topic));
    }

    /**
     * @param Session  $session
     * @param string   $topic
     * @param callable $callback
     */
    public function unsubscribe(Session $session, $topic, callable $callback)
    {
        $requestID = Util::generateID();

        $subscriptionID = null;
        foreach ($this->subscriptions as $subscription) {
            if ($subscription->getTopic() === $topic && $subscription->getCallback() === $callback) {
                $subscriptionID = $subscription->getSubscriptionID();
                break;
            }
        }

        if ($subscriptionID) {
            $session->send(new UnsubscribeMessage($requestID, $subscriptionID));
        }
    }

    /**
     * @param SubscribedMessage $message
     */
    private function processSubscribedMessage(SubscribedMessage $message)
    {
        foreach ($this->subscriptions as $key => $subscription) {
            if ($subscription->getSubscribeRequestID() === $message->getRequestID()) {
                $subscription->setSubscriptionID($message->getSubscriptionID());
                break;
            }
        }
    }

    /**
     * @param UnsubscribedMessage $message
     */
    private function processUnsubscribedMessage(UnsubscribedMessage $message)
    {
        foreach ($this->subscriptions as $key => $subscription) {
            if ($subscription->getUnsubscribeRequestID() === $message->getRequestID()) {
                unset($this->subscriptions[$key]);
                break;
            }
        }
    }

    /**
     * @param EventMessage $message
     */
    private function processEventMessage(EventMessage $message)
    {
        foreach ($this->subscriptions as $key => $subscription) {
            if ($subscription->getSubscriptionID() === $message->getSubscriptionID()) {
                call_user_func(
                    $subscription->getCallback(),
                    $message->getArguments(),
                    $message->getArgumentsKw(),
                    $message->getDetails(),
                    $message->getPublicationID()
                );
                break;
            }
        }
    }

    /**
     * @param ErrorMessage $message
     */
    private function processErrorMessage(ErrorMessage $message)
    {
        switch ($message->getErrorMessageCode()) {
            case MessageCode::_SUBSCRIBE:
                $this->processErrorMessageFromSubscribe($message);
                break;
            case MessageCode::_UNSUBSCRIBE:
                $this->processErrorMessageFromUnsubscribe($message);
                break;
        }
    }

    /**
     * @param ErrorMessage $message
     */
    private function processErrorMessageFromSubscribe(ErrorMessage $message)
    {
        foreach ($this->subscriptions as $key => $subscription) {
            if ($subscription->getSubscribeRequestID() === $message->getErrorRequestID()) {
                unset($this->subscriptions[$key]);
                break;
            }
        }
    }

    /**
     * @param ErrorMessage $message
     */
    private function processErrorMessageFromUnsubscribe(ErrorMessage $message)
    {
        foreach ($this->subscriptions as $key => $subscription) {
            if ($subscription->getUnsubscribeRequestID() === $message->getErrorRequestID()) {
                //TODO are we need delete subscription
                unset($this->subscriptions[$key]);
                break;
            }
        }
    }
}