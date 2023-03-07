<?php

namespace PE\Component\WAMP\Router\Authentication\Method;

use PE\Component\WAMP\Message\AuthenticateMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Router\SessionInterface;

interface MethodInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param SessionInterface $session
     * @param HelloMessage $message
     */
    public function processHelloMessage(SessionInterface $session, HelloMessage $message): void;

    /**
     * @param SessionInterface $session
     * @param AuthenticateMessage $message
     */
    public function processAuthenticateMessage(SessionInterface $session, AuthenticateMessage $message): void;
}
