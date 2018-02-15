<?php

namespace PE\Component\WAMP\Client\Authentication\Method;

use PE\Component\WAMP\Message\ChallengeMessage;
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
    public function processChallengeMessage(Session $session, ChallengeMessage $message)
    {
        // TODO: Implement processChallengeMessage() method.
    }
}