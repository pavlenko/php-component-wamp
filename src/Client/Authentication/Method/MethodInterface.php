<?php

namespace PE\Component\WAMP\Client\Authentication\Method;

use PE\Component\WAMP\Client\Session;
use PE\Component\WAMP\Message\ChallengeMessage;
use PE\Component\WAMP\Message\HelloMessage;

interface MethodInterface
{
    public function getName(): string;

    public function processHelloMessage(Session $session, HelloMessage $message): void;

    public function processChallengeMessage(Session $session, ChallengeMessage $message): void;
}
