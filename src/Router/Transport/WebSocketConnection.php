<?php

namespace PE\Component\WAMP\Router\Transport;

use Ratchet\ConnectionInterface;
use PE\Component\WAMP\Connection\Connection;
use PE\Component\WAMP\Message\Message;
use Symfony\Component\HttpFoundation\Session\Session;

final class WebSocketConnection extends Connection
{
    /**
     * @var ConnectionInterface
     */
    private ConnectionInterface $connection;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @inheritDoc
     */
    public function send(Message $message): void
    {
        $this->connection->send($this->getSerializer()->serialize($message));
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        $this->connection->close();
    }

    /**
     * @inheritDoc
     */
    public function ping(): void
    {
        // TODO: Implement ping() method.
    }

    /**
     * @inheritDoc
     */
    public function getTransportDetails(): array
    {
        return [
            'type' => 'Ratchet/0.4.1'
        ];
    }

    public function getSession(): ?Session
    {
        return $this->connection->Session ?? null;
    }
}
