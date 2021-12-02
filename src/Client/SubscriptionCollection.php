<?php

namespace PE\Component\WAMP\Client;

class SubscriptionCollection
{
    /**
     * @var Subscription[]
     */
    private array $subscriptions = [];

    /**
     * @param Subscription $subscription
     */
    public function add(Subscription $subscription): void
    {
        $this->subscriptions[spl_object_hash($subscription)] = $subscription;
    }

    /**
     * @param Subscription $subscription
     */
    public function remove(Subscription $subscription): void
    {
        if ($key = array_search($subscription, $this->subscriptions, true)) {
            unset($this->subscriptions[$key]);
        }
    }

    /**
     * @param string $topic
     * @param callable $callback
     *
     * @return Subscription|null
     */
    public function findByTopicAndCallable(string $topic, callable $callback): ?Subscription
    {
        $filtered = array_filter($this->subscriptions, function (Subscription $subscription) use ($topic, $callback) {
            return $subscription->getTopic() === $topic && $subscription->getCallback() === $callback;
        });

        return current($filtered) ?: null;
    }

    /**
     * @param int $id
     *
     * @return Subscription|null
     */
    public function findBySubscribeRequestID(int $id): ?Subscription
    {
        $filtered = array_filter($this->subscriptions, function (Subscription $subscription) use ($id) {
            return $subscription->getSubscribeRequestID() === $id;
        });

        return current($filtered) ?: null;
    }

    /**
     * @param int $id
     *
     * @return Subscription|null
     */
    public function findByUnsubscribeRequestID(int $id): ?Subscription
    {
        $filtered = array_filter($this->subscriptions, function (Subscription $subscription) use ($id) {
            return $subscription->getUnsubscribeRequestID() === $id;
        });

        return current($filtered) ?: null;
    }

    /**
     * @param int $id
     *
     * @return Subscription|null
     */
    public function findBySubscriptionID(int $id): ?Subscription
    {
        $filtered = array_filter($this->subscriptions, function (Subscription $subscription) use ($id) {
            return $subscription->getSubscriptionID() === $id;
        });

        return current($filtered) ?: null;
    }
}