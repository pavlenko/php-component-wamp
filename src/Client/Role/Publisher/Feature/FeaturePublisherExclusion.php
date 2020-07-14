<?php

namespace PE\Component\WAMP\Client\Role\Publisher\Feature;

use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\PublishMessage;

final class FeaturePublisherExclusion implements FeatureInterface
{
    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'publisher_exclusion';
    }

    /**
     * @inheritDoc
     */
    public function onMessageSend(Message $message)
    {
        if ($message instanceof PublishMessage) {
            $message->setOption('exclude_me', true);
        }
    }
}
