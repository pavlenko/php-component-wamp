<?php

namespace PE\Component\WAMP\Message;

use PE\Component\WAMP\MessageCode;

/**
 * Unsubscribe request sent by a Subscriber to a Broker to unsubscribe a subscription.
 *
 * <code>[UNSUBSCRIBE, Request|id, SUBSCRIBED.Subscription|id]</code>
 */
class UnsubscribeMessage extends Message
{
    use RequestID;

    /**
     * @var int
     */
    private $subscriptionID;

    /**
     * @param int $requestID
     * @param int $subscriptionID
     */
    public function __construct($requestID, $subscriptionID)
    {
        $this->setRequestID($requestID);
        $this->setSubscriptionID($subscriptionID);
    }

    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return MessageCode::_UNSUBSCRIBE;
    }

    /**
     * @inheritDoc
     */
    public function getParts()
    {
        return [$this->getRequestID(), $this->getSubscriptionID()];
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
     *
     * @return self
     */
    public function setSubscriptionID($subscriptionID)
    {
        $this->subscriptionID = (int) $subscriptionID;
        return $this;
    }
}