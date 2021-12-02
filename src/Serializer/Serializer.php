<?php

namespace PE\Component\WAMP\Serializer;

use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\MessageFactory;

final class Serializer implements SerializerInterface
{
    /**
     * @inheritDoc
     */
    public function serialize(Message $message): string
    {
        return json_encode($message);
    }

    /**
     * @inheritDoc
     */
    public function deserialize(string $message): Message
    {
        $data = json_decode($message, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException('Cannot deserialize');
        }

        return MessageFactory::createFromArray($data);
    }
}