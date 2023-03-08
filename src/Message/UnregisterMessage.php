<?php

namespace PE\Component\WAMP\Message;

/**
 * A Callees request to unregister a previously established registration.
 *
 * <code>[UNREGISTER, Request|id, REGISTERED.Registration|id]</code>
 */
final class UnregisterMessage extends Message
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
        return self::CODE_UNREGISTER;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'UNREGISTER';
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
    public function setRegistrationID(int $registrationID): UnregisterMessage
    {
        $this->registrationID = $registrationID;
        return $this;
    }
}
