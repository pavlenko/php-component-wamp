<?php

namespace PE\Component\WAMP\Client;

/**
 * @codeCoverageIgnore
 */
final class SubscriptionCollection
{
    /**
     * @var Subscription[]
     */
    private array $subscriptions = [];

    public function add(Subscription $subscription): void
    {
        $this->subscriptions[spl_object_hash($subscription)] = $subscription;
    }

    public function remove(Subscription $subscription): void
    {
        if ($key = array_search($subscription, $this->subscriptions, true)) {
            unset($this->subscriptions[$key]);
        }
    }

    public function findByTopicAndCallable(string $topic, \Closure $callback): ?Subscription
    {
        $filtered = array_filter($this->subscriptions, function (Subscription $subscription) use ($topic, $callback) {
            return $subscription->getTopic() === $topic && $subscription->getCallback() === $callback;
        });

        return current($filtered) ?: null;
    }

    public function findBySubscribeRequestID(int $id): ?Subscription
    {
        $filtered = array_filter($this->subscriptions, function (Subscription $subscription) use ($id) {
            return $subscription->getSubscribeRequestID() === $id;
        });

        return current($filtered) ?: null;
    }

    public function findByUnsubscribeRequestID(int $id): ?Subscription
    {
        $filtered = array_filter($this->subscriptions, function (Subscription $subscription) use ($id) {
            return $subscription->getUnsubscribeRequestID() === $id;
        });

        return current($filtered) ?: null;
    }

    public function findBySubscriptionID(int $id): ?Subscription
    {
        $filtered = array_filter($this->subscriptions, function (Subscription $subscription) use ($id) {
            return $subscription->getSubscriptionID() === $id;
        });

        return current($filtered) ?: null;
    }
}