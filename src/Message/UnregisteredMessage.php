<?php

namespace PE\Component\WAMP\Message;

/**
 * Acknowledge sent by a Dealer to a Callee for successful unregistration.
 *
 * <code>[UNREGISTERED, UNREGISTER.Request|id]</code>
 */
final class UnregisteredMessage extends Message
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
        return self::CODE_UNREGISTERED;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'UNREGISTERED';
    }

    /**
     * @inheritDoc
     */
    public function getParts(): array
    {
        return [$this->getRequestID()];
    }
}
