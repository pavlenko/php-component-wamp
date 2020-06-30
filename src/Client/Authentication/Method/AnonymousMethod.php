<?php

namespace PE\Component\WAMP\Client\Authentication\Method;

use PE\Component\WAMP\Client\Session;
use PE\Component\WAMP\Message\ChallengeMessage;

class AnonymousMethod implements MethodInterface
{
    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'anonymous';
    }

    /**
     * @inheritDoc
     */
    public function processChallengeMessage(Session $session, ChallengeMessage $message)
    {
        // DO NOTHING
    }
}
