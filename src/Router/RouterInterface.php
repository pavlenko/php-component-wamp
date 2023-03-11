<?php

namespace PE\Component\WAMP\Router;

use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\Message\Message;

interface RouterInterface
{
    public function processMessageSend(ConnectionInterface $connection, Message $message): void;
}