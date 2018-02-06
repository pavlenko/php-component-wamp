<?php

namespace PE\Component\WAMP\Connection;

use PE\Component\WAMP\Serializer\SerializerInterface;

abstract class Connection implements ConnectionInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var bool
     */
    private $trusted;

    /**
     * @inheritDoc
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * @inheritDoc
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isTrusted()
    {
        return (bool) $this->trusted;
    }

    /**
     * @inheritDoc
     */
    public function setTrusted($trusted)
    {
        $this->trusted = (bool) $trusted;
        return $this;
    }
}