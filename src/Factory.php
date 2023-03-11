<?php

namespace PE\Component\WAMP;

use PE\Component\WAMP\Client\Client;
use PE\Component\WAMP\Client\Session\Session as ClientSession;
use PE\Component\WAMP\Client\Session\SessionInterface as ClientSessionInterface;
use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\Router\Router;
use PE\Component\WAMP\Router\Session\Session as RouterSession;
use PE\Component\WAMP\Router\Session\SessionInterface as RouterSessionInterface;

final class Factory implements FactoryInterface
{
    public function createClientSession(ConnectionInterface $connection, Client $client): ClientSessionInterface
    {
        return new ClientSession($connection, $client);
    }

    public function createRouterSession(ConnectionInterface $connection, Router $router): RouterSessionInterface
    {
        return new RouterSession($connection, $router);
    }
}