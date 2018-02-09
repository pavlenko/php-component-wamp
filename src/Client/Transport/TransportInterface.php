<?php

namespace PE\Component\WAMP\Client\Transport;

use PE\Component\WAMP\Client\ClientInterface;
use React\EventLoop\LoopInterface;

interface TransportInterface
{
    /**
     * @param ClientInterface $client
     * @param LoopInterface   $loop
     */
    public function start(ClientInterface $client, LoopInterface $loop);
}