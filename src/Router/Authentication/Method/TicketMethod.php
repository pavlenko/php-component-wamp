<?php

namespace PE\Component\WAMP\Router\Authentication\Method;

use PE\Component\WAMP\Message\AuthenticateMessage;
use PE\Component\WAMP\Message\ChallengeMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Session;

class TicketMethod implements MethodInterface
{
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
    public function processHelloMessage(Session $session, HelloMessage $message)
    {
        $session->send(new ChallengeMessage($this->getName(), []));
    }

    /**
     * @inheritDoc
     */
    public function processAuthenticateMessage(Session $session, AuthenticateMessage $message)
    {
        // TODO: Implement processAuthenticateMessage() method.
    }
}