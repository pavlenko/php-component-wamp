<?php

namespace PE\Component\WAMP\Router;

use PE\Component\WAMP\Message\Message;

interface SessionInterface
{
    public function send(Message $message): void;
}