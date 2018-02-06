<?php

namespace PE\Component\WAMP;

use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\Message\Message;

abstract class Session
{
    /**
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * @var int
     */
    private $id;

    /**
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param ConnectionInterface $connection
     */
    public function setConnection(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return int
     */
    public function getSessionID()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setSessionID($id)
    {
        $this->id = $id;
    }

    /**
     * @param Message $message
     */
    abstract public function send(Message $message);

    /**
     * Shutdown session
     */
    public function shutdown()
    {
        $this->connection->close();
    }
}