<?php

namespace PE\Component\WAMP\Router\Transport;

use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Connection\Connection;

class LongPollConnection extends Connection
{
    /**
     * @inheritDoc
     */
    public function send(Message $message)
    {
        // TODO: Implement send() method.
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