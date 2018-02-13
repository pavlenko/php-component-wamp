<?php

namespace PE\Component\WAMP\Router\Transport;

use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Connection\Connection;
use React\Promise\Deferred;

class LongPollConnection extends Connection
{
    /**
     * @var Deferred
     */
    private $deferred;

    /**
     * @return Deferred
     */
    public function getDeferred()
    {
        return $this->deferred;
    }

    /**
     * @param Deferred|null $deferred
     */
    public function setDeferred($deferred = null)
    {
        $this->deferred = $deferred;
    }

    /**
     * @inheritDoc
     */
    public function send(Message $message)
    {
        if ($this->deferred) {
            $this->deferred->resolve($this->getSerializer()->serialize($message));
        }
    }

    /**
     * @inheritDoc
     */
    public function close()
    {
        // TODO: Implement close() method.
    }

    /**
     * @inheritDoc
     */
    public function ping()
    {
        // TODO: Implement ping() method.
    }

    /**
     * @inheritDoc
     */
    public function getTransportDetails()
    {
        // TODO: Implement getTransportDetails() method.
    }
}