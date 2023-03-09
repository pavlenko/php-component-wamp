<?php

namespace PE\Component\WAMP\Router\Authentication\Method;

use PE\Component\WAMP\Message\AuthenticateMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Router\SessionInterface;

interface MethodInterface
{
    /**
     * Handle HELLO message from client
     *
     * @param SessionInterface $session
     * @param HelloMessage $message
     *
     * @return bool Return false for bypass to other methods
     */
    public function processHelloMessage(SessionInterface $session, HelloMessage $message): bool;

    /**
     * Handle AUTHENTICATE message
     *
     * @param SessionInterface $session
     * @param AuthenticateMessage $message
     *
     * @return bool Return false for bypass to other methods
     */
    public function processAuthenticateMessage(SessionInterface $session, AuthenticateMessage $message): bool;
}
