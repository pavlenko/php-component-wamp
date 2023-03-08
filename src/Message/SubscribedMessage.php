<?php

namespace PE\Component\WAMP\Message;

/**
 * Acknowledge sent by a Broker to a Subscriber to acknowledge a subscription.
 *
 * <code>[SUBSCRIBED, SUBSCRIBE.Request|id, Subscription|id]</code>
 *
 * @codeCoverageIgnore
 */
final class SubscribedMessage extends Message
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
        return self::CODE_SUBSCRIBED;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'SUBSCRIBED';
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
    public function setSubscriptionID(int $subscriptionID): SubscribedMessage
    {
        $this->subscriptionID = $subscriptionID;
        return $this;
    }
}
