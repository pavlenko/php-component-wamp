<?php

namespace PE\Component\WAMP\Message;

use PE\Component\WAMP\MessageCode;

/**
 * The INTERRUPT message is used with the Call Canceling advanced feature.
 * Upon receiving a cancel for a pending call, a Dealer will issue an interrupt to the Callee.
 *
 * <code>[INTERRUPT, INVOCATION.Request|id, Options|dict]</code>
 */
class InterruptMessage extends Message
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
        return MessageCode::_INTERRUPT;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'INTERRUPT';
    }

    /**
     * @inheritDoc
     */
    public function getParts()
    {
        return [$this->getRequestID(), $this->getOptions()];
    }
}