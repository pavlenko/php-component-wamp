<?php

namespace PE\Component\WAMP\Client\Role;

use PE\Component\WAMP\Message\Message;

interface PublisherFeatureInterface
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
