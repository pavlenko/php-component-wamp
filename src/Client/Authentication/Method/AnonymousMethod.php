<?php

namespace PE\Component\WAMP\Client\Authentication\Method;

use PE\Component\WAMP\Client\Session\SessionInterface;
use PE\Component\WAMP\Message\ChallengeMessage;
use PE\Component\WAMP\Message\HelloMessage;

final class AnonymousMethod implements MethodInterface
{
    public function getName(): string
    {
        return 'anonymous';
    }

    public function processHelloMessage(SessionInterface $session, HelloMessage $message): void
    {
        // DO NOTHING
    }

    public function processChallengeMessage(SessionInterface $session, ChallengeMessage $message): void
    {
        // DO NOTHING
    }
}
