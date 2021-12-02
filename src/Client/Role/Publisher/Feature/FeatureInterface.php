<?php

namespace PE\Component\WAMP\Client\Role\Publisher\Feature;

use PE\Component\WAMP\Message\Message;

interface FeatureInterface
{
    public function getName(): string;

    public function onMessageSend(Message $message): void;
}
