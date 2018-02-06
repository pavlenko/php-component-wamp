<?php

namespace PE\Component\WAMP\Message;

use PE\Component\WAMP\MessageCode;

/**
 * Sent by a Publisher to a Broker to publish an event.
 *
 * <code>[PUBLISH, Request|id, Options|dict, Topic|uri]</code>
 * <code>[PUBLISH, Request|id, Options|dict, Topic|uri, Arguments|list]</code>
 * <code>[PUBLISH, Request|id, Options|dict, Topic|uri, Arguments|list, ArgumentsKw|dict]</code>
 */
class PublishMessage extends Message implements ActionInterface
{
    use RequestID;
    use Options;
    use Arguments;

    /**
     * @var string
     */
    private $topic;

    /**
     * @param int        $requestID
     * @param array      $options
     * @param string     $topic
     * @param array|null $arguments
     * @param array|null $argumentsKw
     */
    public function __construct($requestID, array $options, $topic, array $arguments = null, array $argumentsKw = null)
    {
        $this->setRequestID($requestID);
        $this->setOptions($options);
        $this->setTopic($topic);
        $this->setArguments($arguments);
        $this->setArgumentsKw($argumentsKw);
    }

    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return MessageCode::_PUBLISH;
    }

    /**
     * @inheritDoc
     */
    public function getParts()
    {
        return array_merge(
            [$this->getRequestID(), $this->getOptions(), $this->getTopic()],
            $this->getArgumentsParts()
        );
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
        return 'publish';
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