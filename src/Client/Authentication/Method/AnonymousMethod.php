<?php

namespace PE\Component\WAMP\Client\Authentication\Method;

use PE\Component\WAMP\Client\Session;
use PE\Component\WAMP\Message\ChallengeMessage;
use PE\Component\WAMP\Message\HelloMessage;

final class AnonymousMethod implements MethodInterface
{
    public function getName(): string
    {
        return 'anonymous';
    }

    public function processHelloMessage(Session $session, HelloMessage $message): void
    {
        // DO NOTHING
    }

    public function processChallengeMessage(Session $session, ChallengeMessage $message): void
    {
        // DO NOTHING
    }
}
