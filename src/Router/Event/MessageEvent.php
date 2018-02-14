<?php

namespace PE\Component\WAMP\Router\Event;

use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Router\Session;
use Symfony\Component\EventDispatcher\Event;

class MessageEvent extends Event
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var Message
     */
    private $message;

    /**
     * @param Session $session
     * @param Message $message
     */
    public function __construct(Session $session, Message $message)
    {
        $this->session = $session;
        $this->message = $message;
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @return Message
     */
    public function getMessage()
    {
        return $this->message;
    }
}