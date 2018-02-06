<?php

namespace PE\Component\WAMP\Message;

use PE\Component\WAMP\MessageCode;

/**
 * Subscribe request sent by a Subscriber to a Broker to subscribe to a topic.
 *
 * <code>[SUBSCRIBE, Request|id, Options|dict, Topic|uri]</code>
 */
class SubscribeMessage extends Message implements ActionInterface
{
    use RequestID;
    use Options;

    /**
     * @var string
     */
    private $topic;

    /**
     * @param int    $requestID
     * @param array  $options
     * @param string $topic
     */
    public function __construct($requestID, array $options, $topic)
    {
        $this->setRequestID($requestID);
        $this->setOptions($options);
        $this->setTopic($topic);
    }

    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return MessageCode::_SUBSCRIBE;
    }

    /**
     * @inheritDoc
     */
    public function getParts()
    {
        return [$this->getRequestID(), $this->getOptions(), $this->getTopic()];
    }

    /**
     * @inheritDoc
     */
    public function getActionUri()
    {
        return $this->getTopic();
    }

    /**
     * @inheritDoc
     */
    public function getActionName()
    {
        return 'subscribe';
    }

    /**
     * @return string
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * @param string $topic
     *
     * @return self
     */
    public function setTopic($topic)
    {
        $this->topic = (string) $topic;
        return $this;
    }
}