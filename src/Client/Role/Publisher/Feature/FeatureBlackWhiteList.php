<?php

namespace PE\Component\WAMP\Client\Role\Publisher\Feature;

use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\PublishMessage;

final class FeatureBlackWhiteList implements FeatureInterface
{
    /**
     * @var BlackWhiteListInterface
     */
    private $config;

    public function getName()
    {
        return 'subscriber_blackwhite_listing';
    }

    public function onMessageSend(Message $message)
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
