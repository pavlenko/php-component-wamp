<?php

namespace PE\Component\WAMP\Client\Transport;

use React\EventLoop\LoopInterface;
use PE\Component\WAMP\Client\Client;

interface TransportInterface
{
    /**
     * @param Client        $client
     * @param LoopInterface $loop
     */
    public function start(Client $client, LoopInterface $loop);
}