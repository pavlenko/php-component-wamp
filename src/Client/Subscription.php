<?php

namespace PE\Component\WAMP\Client;

class Subscription
{
    /**
     * @var string
     */
    private $topic;

    /**
     * @var callable
     */
    private $callback;

    /**
     * @var int
     */
    private $subscribeRequestID;

    /**
     * @var int
     */
    private $unsubscribeRequestID;

    /**
     * @var int
     */
    private $subscriptionID;

    /**
     * @param string   $topic
     * @param callable $callback
     */
    public function __construct($topic, callable $callback)
    {
        $this->topic    = $topic;
        $this->callback = $callback;
    }

    /**
     * @return string
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * @return callable
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @return int
     */
    public function getSubscribeRequestID()
    {
        return $this->subscribeRequestID;
    }

    /**
     * @param int $subscribeRequestID
     */
    public function setSubscribeRequestID($subscribeRequestID)
    {
        $this->subscribeRequestID = (int) $subscribeRequestID;
    }

    /**
     * @return int
     */
    public function getUnsubscribeRequestID()
    {
        return $this->unsubscribeRequestID;
    }

    /**
     * @param int $unsubscribeRequestID
     */
    public function setUnsubscribeRequestID($unsubscribeRequestID)
    {
        $this->unsubscribeRequestID = (int) $unsubscribeRequestID;
    }

    /**
     * @return int
     */
    public function getSubscriptionID()
    {
        return $this->subscriptionID;
    }

    /**
     * @param int $subscriptionID
     */
    public function setSubscriptionID($subscriptionID)
    {
        $this->subscriptionID = (int) $subscriptionID;
    }
}