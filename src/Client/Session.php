<?php

namespace PE\Component\WAMP\Client;

use PE\Component\WAMP\Client\Event\Events;
use PE\Component\WAMP\Client\Event\MessageEvent;
use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\Message\Message;

class Session extends \PE\Component\WAMP\Session
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(ConnectionInterface $connection, Client $client)
    {
        parent::__construct($connection);
        $this->client = $client;
    }

    /**
     * @inheritDoc
     */
    public function send(Message $message)
    {
        $this->client->getDispatcher()->dispatch(Events::MESSAGE_SEND, new MessageEvent($this, $message));

        $this->getConnection()->send($message);
    }
}