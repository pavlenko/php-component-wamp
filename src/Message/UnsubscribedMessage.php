<?php

namespace PE\Component\WAMP\Message;

use PE\Component\WAMP\MessageCode;

/**
 * Acknowledge sent by a Broker to a Subscriber to acknowledge unsubscription.
 *
 * <code>[UNSUBSCRIBED, UNSUBSCRIBE.Request|id]</code>
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
        return MessageCode::_UNSUBSCRIBED;
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
