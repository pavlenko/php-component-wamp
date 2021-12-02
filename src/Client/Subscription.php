<?php

namespace PE\Component\WAMP\Client;

use React\Promise\Deferred;

class Subscription
{
    /**
     * @var string
     */
    private string $topic;

    /**
     * @var callable
     */
    private $callback;

    /**
     * @var int
     */
    private int $subscribeRequestID;

    /**
     * @var int
     */
    private int $unsubscribeRequestID;

    /**
     * @var int
     */
    private int $subscriptionID;

    /**
     * @var Deferred
     */
    private Deferred $subscribeDeferred;

    /**
     * @var Deferred
     */
    private Deferred $unsubscribeDeferred;

    /**
     * @param string $topic
     * @param callable $callback
     */
    public function __construct(string $topic, callable $callback)
    {
        $this->topic    = $topic;
        $this->callback = $callback;
    }

    /**
     * @return string
     */
    public function getTopic(): string
    {
        return $this->topic;
    }

    /**
     * @return callable
     */
    public function getCallback(): callable
    {
        return $this->callback;
    }

    /**
     * @return int
     */
    public function getSubscribeRequestID(): int
    {
        return $this->subscribeRequestID;
    }

    /**
     * @param int $subscribeRequestID
     */
    public function setSubscribeRequestID(int $subscribeRequestID): void
    {
        $this->subscribeRequestID = $subscribeRequestID;
    }

    /**
     * @return int
     */
    public function getUnsubscribeRequestID(): int
    {
        return $this->unsubscribeRequestID;
    }

    /**
     * @param int $unsubscribeRequestID
     */
    public function setUnsubscribeRequestID(int $unsubscribeRequestID)
    {
        $this->unsubscribeRequestID = $unsubscribeRequestID;
    }

    /**
     * @return int
     */
    public function getSubscriptionID(): int
    {
        return $this->subscriptionID;
    }

    /**
     * @param int $subscriptionID
     */
    public function setSubscriptionID(int $subscriptionID): void
    {
        $this->subscriptionID = $subscriptionID;
    }

    /**
     * @return Deferred
     */
    public function getSubscribeDeferred(): Deferred
    {
        return $this->subscribeDeferred;
    }

    /**
     * @param Deferred $deferred
     */
    public function setSubscribeDeferred(Deferred $deferred): void
    {
        $this->subscribeDeferred = $deferred;
    }

    /**
     * @return Deferred
     */
    public function getUnsubscribeDeferred(): Deferred
    {
        return $this->unsubscribeDeferred;
    }

    /**
     * @param Deferred $deferred
     */
    public function setUnsubscribeDeferred(Deferred $deferred): void
    {
        $this->unsubscribeDeferred = $deferred;
    }
}