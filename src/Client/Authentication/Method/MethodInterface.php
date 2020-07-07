<?php

namespace PE\Component\WAMP\Client\Authentication\Method;

use PE\Component\WAMP\Client\Session;
use PE\Component\WAMP\Message\ChallengeMessage;
use PE\Component\WAMP\Message\HelloMessage;

interface MethodInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param Session      $session
     * @param HelloMessage $message
     */
    public function processHelloMessage(Session $session, HelloMessage $message);

    /**
     * @param Session          $session
     * @param ChallengeMessage $message
     */
    public function processChallengeMessage(Session $session, ChallengeMessage $message);
}
