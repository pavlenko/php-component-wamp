<?php

namespace PE\Component\WAMP;

use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\Message\Message;

abstract class Session
{
    /**
     * Associated connection
     *
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * Generated session id
     *
     * @var int
     */
    private $id;

    /**
     * Session stored data
     *
     * @var array
     */
    private $data = [];

    /**
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @inheritDoc
     */
    public function __get($name)
    {
        return array_key_exists($name, $this->data)
            ? $this->data[$name]
            : null;
    }

    /**
     * @inheritDoc
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * @inheritDoc
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->data);
    }

    /**
     * @inheritDoc
     */
    public function __unset($name)
    {
        unset($this->data[$name]);
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