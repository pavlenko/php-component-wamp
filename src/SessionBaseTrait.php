<?php

namespace PE\Component\WAMP;

use PE\Component\WAMP\Connection\ConnectionInterface;

trait SessionBaseTrait
{
    private ConnectionInterface $connection;
    private int $id;
    private array $data = [];

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public function __get(string $name)
    {
        return array_key_exists($name, $this->data) ? $this->data[$name] : null;
    }

    public function __set(string $name, $value): void
    {
        $this->data[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return array_key_exists($name, $this->data);
    }

    public function __unset(string $name): void
    {
        unset($this->data[$name]);
    }

    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }

    public function setConnection(ConnectionInterface $connection): void
    {
        $this->connection = $connection;
    }

    public function getSessionID(): int
    {
        return $this->id;
    }

    public function setSessionID(int $id): void
    {
        $this->id = $id;
    }

    public function shutdown(): void
    {
        $this->connection->close();
    }
}
