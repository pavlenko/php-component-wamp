<?php

namespace PE\Component\WAMP\Message;

/**
 * @codeCoverageIgnore
 */
trait RequestID
{
    /**
     * @var int
     */
    private int $requestID = 0;

    /**
     * @return int
     */
    public function getRequestID(): int
    {
        return $this->requestID;
    }

    /**
     * @param int $requestID
     *
     * @return self
     */
    public function setRequestID(int $requestID): self
    {
        $this->requestID = $requestID;
        return $this;
    }
}
