<?php

namespace PE\Component\WAMP\Message;

use PE\Component\WAMP\MessageCode;

/**
 * Subscribe request sent by a Subscriber to a Broker to subscribe to a topic.
 *
 * <code>[SUBSCRIBE, Request|id, Options|dict, Topic|uri]</code>
 */
final class SubscribeMessage extends Message implements ActionInterface
{
    use RequestID;
    use Options;

    /**
     * @var string
     */
    private string $topic;

    /**
     * @param int $requestID
     * @param array  $options
     * @param string $topic
     */
    public function __construct(int $requestID, array $options, string $topic)
    {
        $this->setRequestID($requestID);
        $this->setOptions($options);
        $this->setTopic($topic);
    }

    /**
     * @inheritDoc
     */
    public function getCode(): int
    {
        return MessageCode::_SUBSCRIBE;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'SUBSCRIBE';
    }

    /**
     * @inheritDoc
     */
    public function getParts(): array
    {
        return [$this->getRequestID(), $this->getOptions(), $this->getTopic()];
    }

    /**
     * @inheritDoc
     */
    public function getActionUri(): string
    {
        return $this->getTopic();
    }

    /**
     * @inheritDoc
     */
    public function getActionName(): string
    {
        return 'subscribe';
    }

    /**
     * @return string
     */
    public function getTopic(): string
    {
        return $this->topic;
    }

    /**
     * @param string $topic
     *
     * @return self
     */
    public function setTopic(string $topic): SubscribeMessage
    {
        $this->topic = (string) $topic;
        return $this;
    }
}
