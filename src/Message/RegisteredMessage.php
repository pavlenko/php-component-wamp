<?php

namespace PE\Component\WAMP\Message;

/**
 * Acknowledge sent by a Dealer to a Callee for successful registration.
 *
 * <code>[REGISTERED, REGISTER.Request|id, Registration|id]</code>
 *
 * @codeCoverageIgnore
 */
final class RegisteredMessage extends Message
{
    use RequestID;

    /**
     * @var int
     */
    private int $registrationID;

    /**
     * @param int $requestID
     * @param int $registrationID
     */
    public function __construct(int $requestID, int $registrationID)
    {
        $this->setRequestID($requestID);
        $this->setRegistrationID($registrationID);
    }

    /**
     * @inheritDoc
     */
    public function getCode(): int
    {
        return self::CODE_REGISTERED;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'REGISTERED';
    }

    /**
     * @inheritDoc
     */
    public function getParts(): array
    {
        return [$this->getRequestID(), $this->getRegistrationID()];
    }

    /**
     * @return int
     */
    public function getRegistrationID(): int
    {
        return $this->registrationID;
    }

    /**
     * @param int $registrationID
     *
     * @return self
     */
    public function setRegistrationID(int $registrationID): RegisteredMessage
    {
        $this->registrationID = (int) $registrationID;
        return $this;
    }
}
