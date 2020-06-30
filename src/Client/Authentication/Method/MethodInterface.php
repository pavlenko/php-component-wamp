<?php

namespace PE\Component\WAMP\Client\Authentication\Method;

use PE\Component\WAMP\Client\Session;
use PE\Component\WAMP\Message\ChallengeMessage;

interface MethodInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param Session          $session
     * @param ChallengeMessage $message
     */
    public function processChallengeMessage(Session $session, ChallengeMessage $message);
}
