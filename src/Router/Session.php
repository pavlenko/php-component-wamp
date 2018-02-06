<?php

namespace PE\Component\WAMP\Router;

use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Router\Event\Events;
use PE\Component\WAMP\Router\Event\MessageEvent;

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
        $this->router->emit(Events::MESSAGE_SEND, new MessageEvent($this, $message));
        $this->getConnection()->send($message);
    }
}