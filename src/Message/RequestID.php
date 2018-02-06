<?php

namespace PE\Component\WAMP\Message;

trait RequestID
{
    /**
     * @var int
     */
    private $requestID = 0;

    /**
     * @return int
     */
    public function getRequestID()
    {
        return $this->requestID;
    }

    /**
     * @param int $requestID
     *
     * @return self
     */
    public function setRequestID($requestID)
    {
        $this->requestID = (int) $requestID;
        return $this;
    }
}