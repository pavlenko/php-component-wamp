<?php

namespace PE\Component\WAMP\Client\Event;

use PE\Component\WAMP\Client\Session;
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