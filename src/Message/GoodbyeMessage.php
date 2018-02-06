<?php

namespace PE\Component\WAMP\Message;

use PE\Component\WAMP\MessageCode;

/**
 * <code>[GOODBYE, Details|dict, Reason|uri]</code>
 */
class GoodbyeMessage extends Message
{
    use Details;

    /**
     * @var string
     */
    private $reason;

    /**
     * @param array  $details
     * @param string $reason
     */
    public function __construct(array $details, $reason)
    {
        $this->setDetails($details);
        $this->setReason($reason);
    }

    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return MessageCode::_GOODBYE;
    }

    /**
     * @inheritDoc
     */
    public function getParts()
    {
        return [$this->getDetails(), $this->getReason()];
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param string $reason
     *
     * @return self
     */
    public function setReason($reason)
    {
        $this->reason = (string) $reason;
        return $this;
    }
}