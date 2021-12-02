<?php

namespace PE\Component\WAMP\Message;

abstract class Message implements \JsonSerializable
{
    /**
     * @return int
     */
    abstract public function getCode(): int;

    /**
     * @return string
     */
    abstract public function getName(): string;

    /**
     * @return array
     */
    abstract public function getParts(): array;

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return array_merge([$this->getCode()], $this->getParts());
    }
}
