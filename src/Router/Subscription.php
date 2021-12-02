<?php

namespace PE\Component\WAMP\Router;

final class Subscription
{
    private Session $session;

    private string $topic;

    public function __construct(Session $session, string $topic)
    {
        $this->session = $session;
        $this->topic   = $topic;
    }

    public function getSession(): Session
    {
        return $this->session;
    }

    public function getTopic(): string
    {
        return $this->topic;
    }
}
