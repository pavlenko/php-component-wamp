<?php

namespace PE\Component\WAMP\Client\Role;

use PE\Component\WAMP\Client\Event\Events;
use PE\Component\WAMP\Client\Event\MessageEvent;
use PE\Component\WAMP\Client\Subscription;
use PE\Component\WAMP\Client\SubscriptionCollection;
use PE\Component\WAMP\Message\ErrorMessage;
use PE\Component\WAMP\Message\EventMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\SubscribedMessage;
use PE\Component\WAMP\Message\SubscribeMessage;
use PE\Component\WAMP\Message\UnsubscribedMessage;
use PE\Component\WAMP\Message\UnsubscribeMessage;
use PE\Component\WAMP\MessageCode;
use PE\Component\WAMP\Session;
use PE\Component\WAMP\Util;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use React\Promise\RejectedPromise;

class Subscriber implements RoleInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @param Session|null $session
     *
     * @TODO split to API & Handler|Module|smth
     */
    public function __construct(Session $session = null)
    {
        $this->session = $session;
    }

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
        $session = $event->getSession();
        $message = $event->getMessage();

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
     * @param string     $topic
     * @param callable   $callback
     * @param array|null $options
     *
     * @return PromiseInterface
     */
    public function subscribe($topic, callable $callback, array $options = null)
    {
        $requestID = Util::generateID();
        $options   = $options ?: [];

        $subscription = new Subscription($topic, $callback);
        $subscription->setSubscribeRequestID($requestID);
        $subscription->setSubscribeDeferred($deferred = new Deferred());

        if (!($this->session->subscriptions instanceof SubscriptionCollection)) {
            $this->session->subscriptions = new SubscriptionCollection();
        }

        $this->session->subscriptions->add($subscription);

        $this->session->send(new SubscribeMessage($requestID, $options, $topic));

        return $deferred->promise();
    }

    /**
     * @param string   $topic
     * @param callable $callback
     *
     * @return PromiseInterface
     *
     * @throws \InvalidArgumentException
     */
    public function unsubscribe($topic, callable $callback)
    {
        $requestID     = Util::generateID();
        $subscriptions = $this->session->subscriptions ?: new SubscriptionCollection();

        if ($subscription = $subscriptions->findByTopicAndCallable($topic, $callback)) {
            $subscription->getSubscribeDeferred()->reject();

            $subscription->setUnsubscribeRequestID($requestID);
            $subscription->setUnsubscribeDeferred($deferred = new Deferred());

            $this->session->send(new UnsubscribeMessage($requestID, $subscription->getSubscriptionID()));

            return $deferred->promise();
        }

        return new RejectedPromise();
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