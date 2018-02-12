<?php

namespace PE\Component\WAMP\Client\Transport;

use PE\Component\WAMP\Client\Client;
use React\EventLoop\LoopInterface;

interface TransportInterface
{
    /**
     * @param Client        $client
     * @param LoopInterface $loop
     */
    public function start(Client $client, LoopInterface $loop);
}