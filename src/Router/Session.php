<?php

namespace PE\Component\WAMP\Router;

use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\Message\Message;

class Session extends \PE\Component\WAMP\Session
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @param ConnectionInterface $connection
     * @param Router              $router
     */
    public function __construct(ConnectionInterface $connection, Router $router)
    {
        parent::__construct($connection);
        $this->router = $router;
    }

    /**
     * @inheritDoc
     */
    public function send(Message $message)
    {
        $this->router->processMessageSend($this->getConnection(), $message);
        $this->getConnection()->send($message);
    }
}