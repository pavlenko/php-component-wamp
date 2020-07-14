<?php

namespace PE\Component\WAMP\Client\Role\Publisher\Feature;

use PE\Component\WAMP\Message\Message;

interface FeatureInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param Message $message
     */
    public function onMessageSend(Message $message);
}
