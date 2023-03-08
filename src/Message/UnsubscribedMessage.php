<?php

namespace PE\Component\WAMP\Message;

/**
 * Acknowledge sent by a Broker to a Subscriber to acknowledge unsubscription.
 *
 * <code>[UNSUBSCRIBED, UNSUBSCRIBE.Request|id]</code>
 *
 * @codeCoverageIgnore
 */
final class UnsubscribedMessage extends Message
{
    use RequestID;

    /**
     * @param int $requestID
     */
    public function __construct(int $requestID)
    {
        $this->setRequestID($requestID);
    }

    /**
     * @inheritDoc
     */
    public function getCode(): int
    {
        return self::CODE_UNSUBSCRIBED;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'UNSUBSCRIBED';
    }

    /**
     * @inheritDoc
     */
    public function getParts(): array
    {
        return [$this->getRequestID()];
    }
}
