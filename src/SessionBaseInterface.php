<?php

namespace PE\Component\WAMP;

use PE\Component\WAMP\Message\Message;

interface SessionBaseInterface
{
    /**
     * Get session data property
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name);

    /**
     * Set session data property
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, $value): void;

    /**
     * Check if session data property is set
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset(string $name): bool;

    /**
     * Remove session data property
     *
     * @param string $name
     */
    public function __unset(string $name): void;

    /**
     * Get session id
     *
     * @return int
     */
    public function getSessionID(): int;

    /**
     * Set session id
     *
     * @param int $id
     */
    public function setSessionID(int $id): void;

    /**
     * Send message to session connection
     *
     * @param Message $message
     */
    public function send(Message $message): void;

    /**
     * Shutdown session (close connection)
     */
    public function shutdown(): void;
}