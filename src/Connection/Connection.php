<?php

namespace PE\Component\WAMP\Connection;

use PE\Component\WAMP\Serializer\SerializerInterface;

/**
 * @codeCoverageIgnore
 */
abstract class Connection implements ConnectionInterface
{
    private SerializerInterface $serializer;

    private bool $trusted;

    public function getSerializer(): SerializerInterface
    {
        return $this->serializer;
    }

    public function setSerializer(SerializerInterface $serializer): self
    {
        $this->serializer = $serializer;
        return $this;
    }

    public function isTrusted(): bool
    {
        return $this->trusted;
    }

    public function setTrusted(bool $trusted): self
    {
        $this->trusted = $trusted;
        return $this;
    }
}