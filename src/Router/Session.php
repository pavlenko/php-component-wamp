<?php

namespace PE\Component\WAMP\Router;

use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\SessionBaseTrait;

final class Session implements SessionInterface
{
    use SessionBaseTrait {
        __construct as public constructor;
    }

    private Router $router;
    private ?string $authMethod;

    public function __construct(ConnectionInterface $connection, Router $router)
    {
        $this->constructor($connection);
        $this->router = $router;
    }

    public function send(Message $message): void
    {
        $this->router->processMessageSend($this->connection, $message);
        $this->connection->send($message);
    }

    public function getAuthMethod(): ?string
    {
        return $this->authMethod;
    }

    public function setAuthMethod(string $authMethod): void
    {
        $this->authMethod = $authMethod;
    }
}
