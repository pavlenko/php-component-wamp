<?php

namespace PE\Component\WAMP\Router\Transport;

use Ratchet\ConnectionInterface;
use PE\Component\WAMP\Connection\Connection;
use PE\Component\WAMP\Message\Message;

class WebSocketConnection extends Connection
{
    /**
     * @var ConnectionInterface
     */
    private $connection;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @inheritDoc
     */
    public function send(Message $message)
    {
        $this->connection->send($this->getSerializer()->serialize($message));
    }

    /**
     * @inheritDoc
     */
    public function close()
    {
        $this->connection->close();
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
        return [
            'type' => 'Ratchet/0.4.1'
        ];
    }

    public function getSession()
    {
        return $this->connection->Session ?? null;
    }
}