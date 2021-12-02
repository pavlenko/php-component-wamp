<?php

namespace PE\Component\WAMP\Router\Transport;

use React\EventLoop\LoopInterface;
use PE\Component\WAMP\Router\Router;

interface TransportInterface
{
    /**
     * @param Router        $router
     * @param LoopInterface $loop
     */
    public function start(Router $router, LoopInterface $loop): void;

    /**
     * Stop the router
     */
    public function stop(): void;
}