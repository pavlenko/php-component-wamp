<?php

namespace PE\Component\WAMP\Connection;

use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Session\Session;

interface ConnectionInterface
{
    public function send(Message $message): void;

    public function close(): void;

    public function ping(): void;

    /**
     * @return mixed
     */
    public function getTransportDetails();

    public function getSerializer(): SerializerInterface;

    public function setSerializer(SerializerInterface $serializer): ConnectionInterface;

    public function isTrusted(): bool;

    public function setTrusted(bool $trusted): ConnectionInterface;

     public function getSession(): Session;
}