<?php

namespace PE\Component\WAMP\Router\Transport;

use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use PE\Component\WAMP\Router\Router;

interface TransportInterface
{
    /**
     * Start transport
     *
     * @param Router $router
     * @param LoopInterface $loop
     * @param LoggerInterface $logger
     */
    public function start(Router $router, LoopInterface $loop, LoggerInterface $logger): void;

    /**
     * Stop the router
     */
    public function stop(): void;
}