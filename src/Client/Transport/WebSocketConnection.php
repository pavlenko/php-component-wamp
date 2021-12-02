<?php

namespace PE\Component\WAMP\Client\Transport;

use Ratchet\Client\WebSocket;
use PE\Component\WAMP\Connection\Connection;
use PE\Component\WAMP\Message\Message;

final class WebSocketConnection extends Connection
{
    private WebSocket $socket;

    public function __construct(WebSocket $socket)
    {
        $this->socket = $socket;
    }

    public function getSession()
    {
        // TODO: Implement getSession() method.
    }

    public function send(Message $message): void
    {
        $this->socket->send($this->getSerializer()->serialize($message));
    }

    public function close(): void
    {
        $this->socket->close();
    }

    public function ping(): void
    {}

    public function getTransportDetails(): array
    {
        return [
            'type' => 'Pawl/0.3.1'
        ];
    }
}