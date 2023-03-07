<?php

namespace PE\Component\WAMP\Client;

use PE\Component\WAMP\Message\Message;

interface SessionInterface
{
    public function send(Message $message): void;
}