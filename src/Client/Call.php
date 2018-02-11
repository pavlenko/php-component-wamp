<?php

namespace PE\Component\WAMP\Client;

use React\Promise\Deferred;

class Call
{
    /**
     * @var int
     */
    private $requestID;

    /**
     * @var Deferred
     */
    private $deferred;

    /**
     * @param int      $requestID
     * @param Deferred $deferred
     */
    public function __construct($requestID, Deferred $deferred)
    {
        $this->requestID = $requestID;
        $this->deferred  = $deferred;
    }

    /**
     * @return int
     */
    public function getRequestID()
    {
        return $this->requestID;
    }

    /**
     * @return Deferred
     */
    public function getDeferred()
    {
        return $this->deferred;
    }
}