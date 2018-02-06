<?php

namespace PE\Component\WAMP\Message;

use PE\Component\WAMP\MessageCode;

/**
 * Acknowledge sent by a Broker to a Subscriber to acknowledge unsubscription.
 *
 * <code>[UNSUBSCRIBED, UNSUBSCRIBE.Request|id]</code>
 */
class UnsubscribedMessage extends Message
{
    use RequestID;

    /**
     * @param int $requestID
     */
    public function __construct($requestID)
    {
        $this->setRequestID($requestID);
    }

    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return MessageCode::_UNSUBSCRIBED;
    }

    /**
     * @inheritDoc
     */
    public function getParts()
    {
        return [$this->getRequestID()];
    }
}