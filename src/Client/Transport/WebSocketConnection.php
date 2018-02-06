<?php

namespace PE\Component\WAMP\Client\Transport;

use Ratchet\Client\WebSocket;
use PE\Component\WAMP\Connection\Connection;
use PE\Component\WAMP\Message\Message;

class WebSocketConnection extends Connection
{
    private $socket;

    /**
     * @param WebSocket $socket
     */
    public function __construct(WebSocket $socket)
    {
        $this->socket = $socket;
    }

    /**
     * @inheritDoc
     */
    public function send(Message $message)
    {
        $this->socket->send($this->getSerializer()->serialize($message));
    }

    /**
     * @inheritDoc
     */
    public function close()
    {
        $this->socket->close();
    }

    /**
     * @inheritDoc
     */
    public function ping()
    {}

    /**
     * @inheritDoc
     */
    public function getTransportDetails()
    {
        return [
            'type' => 'Pawl/0.3.1'
        ];
    }
}