<?php

namespace PE\Component\WAMP\Client;

use PE\Component\WAMP\Client\Transport\TransportInterface;
use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\Message\Message;
use Psr\Log\LoggerInterface;

interface ClientInterface
{
    const RECONNECT_TIMEOUT  = 1.5;
    const RECONNECT_ATTEMPTS = 15;

    /**
     * Handle connection opened, called from transport
     *
     * @param ConnectionInterface $connection
     */
    public function onOpen(ConnectionInterface $connection);

    /**
     * Handle connection close, called from transport
     *
     * @param string $reason
     */
    public function onClose($reason);

    /**
     * Handle message received, called from transport
     *
     * @param Message $message
     */
    public function onMessageReceived(Message $message);

    /**
     * Handle message send, called from session
     *
     * @param Message $message
     */
    public function onMessageSend(Message $message);

    /**
     * Handle connection error
     *
     * @param \Exception $exception
     */
    public function onError(\Exception $exception);

    /**
     * Set transport
     *
     * @param TransportInterface $transport
     */
    public function setTransport(TransportInterface $transport);

    /**
     * Set logger
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger);

    /**
     * Set reconnect timeout in seconds
     *
     * @param int $timeout
     */
    public function setReconnectTimeout($timeout);

    /**
     * Set reconnect attempts
     *
     * @param int $attempts
     */
    public function setReconnectAttempts($attempts);

    /**
     * Start client
     */
    public function connect();
}