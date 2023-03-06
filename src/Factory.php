<?php

namespace PE\Component\WAMP;

use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\Router\Router;
use PE\Component\WAMP\Router\Session as SessionRouter;

final class Factory implements FactoryInterface
{
    public function createRouterSession(ConnectionInterface $connection, Router $router): SessionRouter
    {
        return new SessionRouter($connection, $router);
    }
}