<?php

namespace PE\Component\WAMP\Message;

/**
 * Sent by a Publisher to a Broker to publish an event.
 *
 * <code>[PUBLISH, Request|id, Options|dict, Topic|uri]</code>
 * <code>[PUBLISH, Request|id, Options|dict, Topic|uri, Arguments|list]</code>
 * <code>[PUBLISH, Request|id, Options|dict, Topic|uri, Arguments|list, ArgumentsKw|dict]</code>
 */
final class PublishMessage extends Message implements ActionInterface
{
    use RequestID;
    use Options;
    use Arguments;

    /**
     * @var string
     */
    private string $topic;

    /**
     * @param int $requestID
     * @param array      $options
     * @param string $topic
     * @param array|null $arguments
     * @param array|null $argumentsKw
     */
    public function __construct(int $requestID, array $options, string $topic, array $arguments = null, array $argumentsKw = null)
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
    public function getCode(): int
    {
        return self::CODE_PUBLISH;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'PUBLISH';
    }

    /**
     * @inheritDoc
     */
    public function getParts(): array
    {
        return array_merge(
            [$this->getRequestID(), $this->getOptions(), $this->getTopic()],
            $this->getArgumentsParts()
        );
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
        return 'publish';
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
    public function setTopic(string $topic): PublishMessage
    {
        $this->topic = $topic;
        return $this;
    }
}
