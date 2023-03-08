<?php

namespace PE\Component\WAMP\Message;

/**
 * The INTERRUPT message is used with the Call Canceling advanced feature.
 * Upon receiving a cancel for a pending call, a Dealer will issue an interrupt to the Callee.
 *
 * <code>[INTERRUPT, INVOCATION.Request|id, Options|dict]</code>
 */
final class InterruptMessage extends Message
{
    use RequestID;
    use Options;

    /**
     * @param int $requestID
     * @param array $options
     */
    public function __construct(int $requestID, array $options)
    {
        $this->setRequestID($requestID);
        $this->setOptions($options);
    }

    /**
     * @inheritDoc
     */
    public function getCode(): int
    {
        return self::CODE_INTERRUPT;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'INTERRUPT';
    }

    /**
     * @inheritDoc
     */
    public function getParts(): array
    {
        return [$this->getRequestID(), $this->getOptions()];
    }
}
