<?php

namespace PE\Component\WAMP\Message;

use PE\Component\WAMP\MessageCode;

/**
 * <code>[GOODBYE, Details|dict, Reason|uri]</code>
 */
final class GoodbyeMessage extends Message
{
    use Details;

    /**
     * @var string
     */
    private string $reason;

    /**
     * @param array  $details
     * @param string $reason
     */
    public function __construct(array $details, string $reason)
    {
        $this->setDetails($details);
        $this->setReason($reason);
    }

    /**
     * @inheritDoc
     */
    public function getCode(): int
    {
        return MessageCode::_GOODBYE;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'GOODBYE';
    }

    /**
     * @inheritDoc
     */
    public function getParts(): array
    {
        return [$this->getDetails(), $this->getReason()];
    }

    /**
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * @param string $reason
     *
     * @return self
     */
    public function setReason(string $reason): GoodbyeMessage
    {
        $this->reason = $reason;
        return $this;
    }
}
