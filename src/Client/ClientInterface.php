<?php

namespace PE\Component\WAMP\Client;

use PE\Component\WAMP\Message\Message;

interface ClientInterface
{
    public function processMessageSend(Message $message): void;
}