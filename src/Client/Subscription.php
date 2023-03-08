<?php

namespace PE\Component\WAMP\Client;

use React\Promise\Deferred;

/**
 * @codeCoverageIgnore
 */
final class Subscription
{
    private string $topic;
    private \Closure $callback;
    private int $subscribeRequestID = 0;
    private int $unsubscribeRequestID = 0;
    private int $subscriptionID = 0;
    private ?Deferred $subscribeDeferred = null;
    private ?Deferred $unsubscribeDeferred = null;

    public function __construct(string $topic, \Closure $callback)
    {
        $this->topic    = $topic;
        $this->callback = $callback;
    }

    public function getTopic(): string
    {
        return $this->topic;
    }

    public function getCallback(): \Closure
    {
        return $this->callback;
    }

    public function getSubscribeRequestID(): int
    {
        return $this->subscribeRequestID;
    }

    public function setSubscribeRequestID(int $subscribeRequestID): void
    {
        $this->subscribeRequestID = $subscribeRequestID;
    }

    public function getUnsubscribeRequestID(): int
    {
        return $this->unsubscribeRequestID;
    }

    public function setUnsubscribeRequestID(int $unsubscribeRequestID)
    {
        $this->unsubscribeRequestID = $unsubscribeRequestID;
    }

    public function getSubscriptionID(): int
    {
        return $this->subscriptionID;
    }

    public function setSubscriptionID(int $subscriptionID): void
    {
        $this->subscriptionID = $subscriptionID;
    }

    public function getSubscribeDeferred(): ?Deferred
    {
        return $this->subscribeDeferred;
    }

    public function setSubscribeDeferred(Deferred $deferred): void
    {
        $this->subscribeDeferred = $deferred;
    }

    public function getUnsubscribeDeferred(): ?Deferred
    {
        return $this->unsubscribeDeferred;
    }

    public function setUnsubscribeDeferred(Deferred $deferred): void
    {
        $this->unsubscribeDeferred = $deferred;
    }
}