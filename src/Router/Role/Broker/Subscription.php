<?php

namespace PE\Component\WAMP\Router\Role\Broker;

use PE\Component\WAMP\Router\SessionInterface;

/**
 * Subscription DTO
 *
 * @codeCoverageIgnore
 */
final class Subscription
{
    private SessionInterface $session;

    private string $topic;

    public function __construct(SessionInterface $session, string $topic)
    {
        $this->session = $session;
        $this->topic   = $topic;
    }

    public function getSession(): SessionInterface
    {
        return $this->session;
    }

    public function getTopic(): string
    {
        return $this->topic;
    }
}