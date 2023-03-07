<?php

namespace PE\Component\WAMP\Client\Transport;

use PE\Component\WAMP\Client\Client;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;

interface TransportInterface
{
    public function start(Client $client, LoopInterface $loop, LoggerInterface $logger): void;
}