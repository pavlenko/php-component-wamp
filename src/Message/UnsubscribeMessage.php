<?php

namespace PE\Component\WAMP\Message;

/**
 * Unsubscribe request sent by a Subscriber to a Broker to unsubscribe a subscription.
 *
 * <code>[UNSUBSCRIBE, Request|id, SUBSCRIBED.Subscription|id]</code>
 */
final class UnsubscribeMessage extends Message
{
    use RequestID;

    /**
     * @var int
     */
    private int $subscriptionID;

    /**
     * @param int $requestID
     * @param int $subscriptionID
     */
    public function __construct(int $requestID, int $subscriptionID)
    {
        $this->setRequestID($requestID);
        $this->setSubscriptionID($subscriptionID);
    }

    /**
     * @inheritDoc
     */
    public function getCode(): int
    {
        return self::CODE_UNSUBSCRIBE;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'UNSUBSCRIBE';
    }

    /**
     * @inheritDoc
     */
    public function getParts(): array
    {
        return [$this->getRequestID(), $this->getSubscriptionID()];
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
     *
     * @return self
     */
    public function setSubscriptionID(int $subscriptionID): UnsubscribeMessage
    {
        $this->subscriptionID = $subscriptionID;
        return $this;
    }
}
