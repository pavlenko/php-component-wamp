<?php

namespace PE\Component\WAMP\Message;

abstract class Message implements \JsonSerializable
{
    /**
     * @return int
     */
    abstract public function getCode();

    /**
     * @return array
     */
    abstract public function getParts();

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return array_merge([$this->getCode()], $this->getParts());
    }
}