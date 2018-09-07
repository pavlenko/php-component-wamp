<?php

namespace PE\Component\WAMP\Connection;

use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Session\Session;

interface ConnectionInterface
{
    /**
     * @param Message $message
     */
    public function send(Message $message);

    /**
     * Close transport
     */
    public function close();

    /**
     * Ping
     */
    public function ping();

    /**
     * @return mixed
     */
    public function getTransportDetails();

    /**
     * Get serializer
     *
     * @return SerializerInterface
     */
    public function getSerializer();

    /**
     * Set serializer
     *
     * @param SerializerInterface $serializer
     *
     * @return self
     */
    public function setSerializer(SerializerInterface $serializer);

    /**
     * Checks if a transport is trusted
     *
     * @return bool
     */
    public function isTrusted();

    /**
     * Set transport as trusted
     *
     * @param bool $trusted
     *
     * @return self
     */
    public function setTrusted($trusted);

    /**
     * @return Session
     */
     public function getSession();
}