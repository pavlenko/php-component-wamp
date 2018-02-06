<?php

namespace PE\Component\WAMP\Message;

use PE\Component\WAMP\MessageCode;

/**
 * Acknowledge sent by a Dealer to a Callee for successful registration.
 *
 * <code>[REGISTERED, REGISTER.Request|id, Registration|id]</code>
 */
class RegisteredMessage extends Message
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
        return MessageCode::_REGISTERED;
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