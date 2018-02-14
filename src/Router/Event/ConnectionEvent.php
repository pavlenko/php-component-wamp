<?php

namespace PE\Component\WAMP\Router\Event;

use PE\Component\WAMP\Router\Session;
use Symfony\Component\EventDispatcher\Event;

class ConnectionEvent extends Event
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }
}