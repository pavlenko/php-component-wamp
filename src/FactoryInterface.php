<?php

namespace PE\Component\WAMP;

use PE\Component\WAMP\Client\Client;
use PE\Component\WAMP\Client\SessionInterface as ClientSessionInterface;
use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\Router\Router;
use PE\Component\WAMP\Router\SessionInterface as RouterSessionInterface;

interface FactoryInterface
{
    /**
     * Create client session
     *
     * @param ConnectionInterface $connection
     * @param Client $client
     *
     * @return ClientSessionInterface
     */
    public function createClientSession(ConnectionInterface $connection, Client $client): ClientSessionInterface;

    /**
     * Create router session
     *
     * @param ConnectionInterface $connection
     * @param Router $router
     *
     * @return RouterSessionInterface
     */
    public function createRouterSession(ConnectionInterface $connection, Router $router): RouterSessionInterface;
}