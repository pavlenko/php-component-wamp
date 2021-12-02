<?php

namespace PE\Component\WAMP\Router\Authentication\Method;

use PE\Component\WAMP\Message\AuthenticateMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Router\Session;

interface MethodInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param Session      $session
     * @param HelloMessage $message
     */
    public function processHelloMessage(Session $session, HelloMessage $message): void;

    /**
     * @param Session             $session
     * @param AuthenticateMessage $message
     */
    public function processAuthenticateMessage(Session $session, AuthenticateMessage $message): void;
}
