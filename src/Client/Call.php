<?php

namespace PE\Component\WAMP\Client;

use React\Promise\Deferred;

class Call
{
    /**
     * @var int
     */
    private int $requestID;

    /**
     * @var Deferred
     */
    private Deferred $deferred;

    /**
     * @param int      $requestID
     * @param Deferred $deferred
     */
    public function __construct(int $requestID, Deferred $deferred)
    {
        $this->requestID = $requestID;
        $this->deferred  = $deferred;
    }

    /**
     * @return int
     */
    public function getRequestID(): int
    {
        return $this->requestID;
    }

    /**
     * @return Deferred
     */
    public function getDeferred(): Deferred
    {
        return $this->deferred;
    }
}