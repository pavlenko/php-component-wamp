<?php

namespace PE\Component\WAMP\Client;

use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\SessionBaseTrait;

final class Session implements SessionInterface
{
    use SessionBaseTrait {
        __construct as public constructor;
    }

    /**
     * @var Client
     */
    private Client $client;

    public function __construct(ConnectionInterface $connection, Client $client)
    {
        $this->constructor($connection);
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