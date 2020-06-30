<?php

namespace PE\Component\WAMP\Client\Authentication\Method;

use PE\Component\WAMP\Client\Session;
use PE\Component\WAMP\Message\AuthenticateMessage;
use PE\Component\WAMP\Message\ChallengeMessage;

class TicketMethod implements MethodInterface
{
    /**
     * @var string
     */
    private $ticket;

    /**
     * @param string $ticket
     */
    public function __construct($ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'ticket';
    }

    /**
     * @inheritDoc
     */
    public function processChallengeMessage(Session $session, ChallengeMessage $message)
    {
        $session->send(new AuthenticateMessage($this->ticket, []));
    }
}
