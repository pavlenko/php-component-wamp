<?php

namespace PE\Component\WAMP\Client\DTO;

use React\Promise\Deferred;

/**
 * @codeCoverageIgnore
 */
final class Call
{
    private int $requestID;
    private Deferred $deferred;

    public function __construct(int $requestID, Deferred $deferred)
    {
        $this->requestID = $requestID;
        $this->deferred  = $deferred;
    }

    public function getRequestID(): int
    {
        return $this->requestID;
    }

    public function getDeferred(): Deferred
    {
        return $this->deferred;
    }
}