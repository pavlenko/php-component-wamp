<?php

namespace PE\Component\WAMP\Message;

use PE\Component\WAMP\MessageCode;

/**
 * The CANCEL message is used with the Call Canceling advanced feature.
 * A Caller can cancel and issued call actively by sending a cancel message to the Dealer.
 *
 * <code>[CANCEL, CALL.Request|id, Options|dict]</code>
 */
class CancelMessage extends Message
{
    use RequestID;
    use Options;

    /**
     * @param int   $requestID
     * @param array $options
     */
    public function __construct($requestID, array $options)
    {
        $this->setRequestID($requestID);
        $this->setOptions($options);
    }

    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return MessageCode::_CANCEL;
    }

    /**
     * @inheritDoc
     */
    public function getParts()
    {
        return [$this->getRequestID(), $this->getOptions()];
    }
}