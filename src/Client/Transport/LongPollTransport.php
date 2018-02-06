<?php

namespace PE\Component\WAMP\Client\Transport;

use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Connection\Connection;

class LongPollTransport extends Connection
{
    /**
     * @inheritDoc
     */
    public function send(Message $message)
    {
        throw new \LogicException('Not yet implemented');
    }

    /**
     * @inheritDoc
     */
    public function close()
    {
        throw new \LogicException('Not yet implemented');
    }

    /**
     * @inheritDoc
     */
    public function ping()
    {
        throw new \LogicException('Not yet implemented');
    }

    /**
     * @inheritDoc
     */
    public function getTransportDetails()
    {
        return ['type' => 'longpoll'];
    }
}