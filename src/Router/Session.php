<?php

namespace PE\Component\WAMP\Router;

use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\Message\Message;

final class Session extends \PE\Component\WAMP\Session
{
    private Router $router;

    private ?string $authMethod;

    public function __construct(ConnectionInterface $connection, Router $router)
    {
        parent::__construct($connection);
        $this->router = $router;
    }

    public function send(Message $message): void
    {
        $this->router->processMessageSend($this->getConnection(), $message);
        $this->getConnection()->send($message);
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
