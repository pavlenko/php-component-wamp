<?php

namespace PE\Component\WAMP\Message;

use PE\Component\WAMP\MessageCode;

/**
 * A Callees request to unregister a previously established registration.
 *
 * <code>[UNREGISTER, Request|id, REGISTERED.Registration|id]</code>
 */
class UnregisterMessage extends Message
{
    use RequestID;

    /**
     * @var int
     */
    private $registrationID;

    /**
     * @param int $requestID
     * @param int $registrationID
     */
    public function __construct($requestID, $registrationID)
    {
        $this->setRequestID($requestID);
        $this->setRegistrationID($registrationID);
    }

    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return MessageCode::_UNREGISTER;
    }

    /**
     * @inheritDoc
     */
    public function getParts()
    {
        return [$this->getRequestID(), $this->getRegistrationID()];
    }

    /**
     * @return int
     */
    public function getRegistrationID()
    {
        return $this->registrationID;
    }

    /**
     * @param int $registrationID
     *
     * @return self
     */
    public function setRegistrationID($registrationID)
    {
        $this->registrationID = (int) $registrationID;
        return $this;
    }
}