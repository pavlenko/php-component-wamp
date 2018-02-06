<?php

namespace PE\Component\WAMP\Client\Authentication;

use PE\Component\WAMP\Message\AuthenticateMessage;
use PE\Component\WAMP\Message\ChallengeMessage;

interface AuthenticatorInterface
{
    /**
     * @param ChallengeMessage $message
     *
     * @return AuthenticateMessage|false
     */
    public function authenticate(ChallengeMessage $message);

    /**
     * @param string $authenticationMethod
     *
     * @return bool
     */
    public function supports($authenticationMethod);
}