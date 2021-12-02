<?php

namespace PE\Component\WAMP\Client;

use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\Message\Message;

class Session extends \PE\Component\WAMP\Session
{
    /**
     * @var Client
     */
    private Client $client;

    public function __construct(ConnectionInterface $connection, Client $client)
    {
        parent::__construct($connection);
        $this->client = $client;
    }

    /**
     * @inheritDoc
     */
    public function send(Message $message): void
    {
        $this->client->processMessageSend($message);
        $this->getConnection()->send($message);
    }

    /**
     * @inheritDoc
     */
    public function setSessionID(int $id): void
    {
        parent::setSessionID($id);
        $this->client->emit(Client::EVENT_SESSION_ESTABLISHED, $this);
    }
}