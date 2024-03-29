<?php

namespace PE\Component\WAMP\Router\Session;

use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Router\RouterInterface;
use PE\Component\WAMP\SessionBaseTrait;

final class Session implements SessionInterface
{
    use SessionBaseTrait {
        __construct as public constructor;
    }

    private RouterInterface $router;

    public function __construct(ConnectionInterface $connection, RouterInterface $router)
    {
        $this->constructor($connection);

        $this->router  = $router;
        $this->session = $connection->getSession();
    }

    public function send(Message $message): void
    {
        $this->router->processMessageSend($this->connection, $message);
        $this->connection->send($message);
    }
}
