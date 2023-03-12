<?php

namespace PE\Component\WAMP\Router\DTO;

use PE\Component\WAMP\Router\Session\SessionInterface;

/**
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
