<?php

namespace PE\Component\WAMP\Router;

class Subscription
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var string
     */
    private $topic;

    /**
     * @param Session $session
     * @param string  $topic
     */
    public function __construct(Session $session, $topic)
    {
        $this->session = $session;
        $this->topic   = $topic;
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @return string
     */
    public function getTopic()
    {
        return $this->topic;
    }
}