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
    private int $subscriptionID = 0;

    public function __construct(SessionInterface $session, string $topic, int $subscriptionID)
    {
        $this->session        = $session;
        $this->topic          = $topic;
        $this->subscriptionID = $subscriptionID;
    }

    public function getSession(): SessionInterface
    {
        return $this->session;
    }

    public function getTopic(): string
    {
        return $this->topic;
    }

    public function getSubscriptionID(): int
    {
        return $this->subscriptionID;
    }
}
