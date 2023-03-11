<?php

namespace PE\Component\WAMP\Client\Authentication\Method;

use PE\Component\WAMP\Client\Session\SessionInterface;
use PE\Component\WAMP\Message\AuthenticateMessage;
use PE\Component\WAMP\Message\ChallengeMessage;
use PE\Component\WAMP\Message\HelloMessage;

final class TicketMethod implements MethodInterface
{
    private string $ticket;

    public function __construct(string $ticket)
    {
        $this->ticket = $ticket;
    }

    public function getName(): string
    {
        return 'ticket';
    }

    public function processHelloMessage(SessionInterface $session, HelloMessage $message): void
    {
        // DO NOTHING
    }

    public function processChallengeMessage(SessionInterface $session, ChallengeMessage $message): void
    {
        $session->send(new AuthenticateMessage($this->ticket, []));
    }
}
