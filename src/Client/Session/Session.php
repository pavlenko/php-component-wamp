<?php

namespace PE\Component\WAMP\Client\Session;

use PE\Component\WAMP\Client\Client;
use PE\Component\WAMP\Client\ClientInterface;
use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\SessionBaseTrait;

final class Session implements SessionInterface
{
    use SessionBaseTrait {
        __construct as public constructor;
    }

    private ClientInterface $client;

    public function __construct(ConnectionInterface $connection, ClientInterface $client)
    {
        $this->constructor($connection);
        $this->client = $client;
    }

    public function send(Message $message): void
    {
        $this->client->processMessageSend($message);
        $this->connection->send($message);
    }
}