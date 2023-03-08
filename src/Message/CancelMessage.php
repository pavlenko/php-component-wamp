<?php

namespace PE\Component\WAMP\Message;

/**
 * The CANCEL message is used with the Call Canceling advanced feature.
 * A Caller can cancel and issued call actively by sending a cancel message to the Dealer.
 *
 * <code>[CANCEL, CALL.Request|id, Options|dict]</code>
 *
 * @codeCoverageIgnore
 */
final class CancelMessage extends Message
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
        return self::CODE_CANCEL;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'CANCEL';
    }

    /**
     * @inheritDoc
     */
    public function getParts(): array
    {
        return [$this->getRequestID(), $this->getOptions()];
    }
}