<?php

namespace Publisher\Feature;

use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\PublishMessage;

final class FeatureBlackWhiteList implements FeatureInterface
{
    /**
     * @var BlackWhiteListInterface
     */
    private BlackWhiteListInterface $config;

    public function getName(): string
    {
        return 'subscriber_blackwhite_listing';
    }

    public function onMessageSend(Message $message): void
    {
        if ($message instanceof PublishMessage) {
            $blackList = $this->config->getBlackListItems($message->getTopic());
            if (!empty($blackList)) {
                $message->setOption($this->config->getBlackListKey(), $blackList);
            }

            $whiteList = $this->config->getWhiteListItems($message->getTopic());
            if (!empty($whiteList)) {
                $message->setOption($this->config->getWhiteListKey(), $whiteList);
            }
        }
    }
}
