<?php

namespace PE\Component\WAMP\Client\Role;

use PE\Component\WAMP\Client\Subscription;
use PE\Component\WAMP\Client\SubscriptionCollection;
use PE\Component\WAMP\Message\SubscribeMessage;
use PE\Component\WAMP\Message\UnsubscribeMessage;
use PE\Component\WAMP\Session;
use PE\Component\WAMP\Util;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use React\Promise\RejectedPromise;

final class SubscriberAPI
{
    /**
     * @var Session
     */
    private Session $session;

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @param string $topic
     * @param callable   $callback
     * @param array|null $options
     *
     * @return PromiseInterface
     */
    public function subscribe(string $topic, callable $callback, array $options = null): PromiseInterface
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
     * @param string $topic
     * @param callable $callback
     *
     * @return PromiseInterface
     *
     * @throws \InvalidArgumentException
     */
    public function unsubscribe(string $topic, callable $callback): PromiseInterface
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
}