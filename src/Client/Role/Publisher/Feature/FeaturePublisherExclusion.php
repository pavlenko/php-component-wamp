<?php

namespace PE\Component\WAMP\Client\Role\Publisher\Feature;

use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\PublishMessage;

final class FeaturePublisherExclusion implements FeatureInterface
{
    public function getName(): string
    {
        return 'publisher_exclusion';
    }

    public function onMessageSend(Message $message): void
    {
        if ($message instanceof PublishMessage) {
            $message->setOption('exclude_me', true);
        }
    }
}
