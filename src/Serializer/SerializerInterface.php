<?php

namespace PE\Component\WAMP\Serializer;

use PE\Component\WAMP\Message\Message;

interface SerializerInterface
{
    /**
     * @param Message $message
     *
     * @return string
     */
    public function serialize(Message $message): string;

    /**
     * @param string $message
     *
     * @return Message
     *
     * @throws \InvalidArgumentException If cannot deserialize or deserialized data invalid
     */
    public function deserialize(string $message): Message;
}