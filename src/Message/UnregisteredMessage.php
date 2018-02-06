<?php

namespace PE\Component\WAMP\Message;

use PE\Component\WAMP\MessageCode;

/**
 * Acknowledge sent by a Dealer to a Callee for successful unregistration.
 *
 * <code>[UNREGISTERED, UNREGISTER.Request|id]</code>
 */
class UnregisteredMessage extends Message
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
        return MessageCode::_UNREGISTERED;
    }

    /**
     * @inheritDoc
     */
    public function getParts()
    {
        return [$this->getRequestID()];
    }
}