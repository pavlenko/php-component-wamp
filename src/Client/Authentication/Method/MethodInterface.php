<?php

namespace PE\Component\WAMP\Client\Authentication\Method;

use PE\Component\WAMP\Client\SessionInterface;
use PE\Component\WAMP\Message\ChallengeMessage;
use PE\Component\WAMP\Message\HelloMessage;

interface MethodInterface
{
    public function getName(): string;

    public function processHelloMessage(SessionInterface $session, HelloMessage $message): void;

    public function processChallengeMessage(SessionInterface $session, ChallengeMessage $message): void;
}
