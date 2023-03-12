<?php

namespace Publisher\Feature;

use PE\Component\WAMP\Message\Message;

/**
 * @deprecated
 */
interface FeatureInterface
{
    public function getName(): string;

    public function onMessageSend(Message $message): void;
}
