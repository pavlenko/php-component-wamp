<?php

namespace PE\Component\WAMP\Router\Authentication\Method;

use PE\Component\WAMP\Message\AuthenticateMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\WelcomeMessage;
use PE\Component\WAMP\Router\SessionInterface;
use PE\Component\WAMP\Util;
use Symfony\Component\HttpFoundation\Session\SessionInterface as SymfonySessionInterface;

final class CookieMethod implements MethodInterface
{
    private array $tokenKeys;

    public function __construct(array $tokenKeys)
    {
        $this->tokenKeys = $tokenKeys;
    }

    public function processHelloMessage(SessionInterface $session, HelloMessage $message): bool
    {
        if ($session->session && $session->session instanceof SymfonySessionInterface) {
            foreach ($this->tokenKeys as $key) {
                $token = $session->session->get($key);
                if (!empty($token)) {
                    $session->token = $token;

                    $sessionID = Util::generateID();

                    $session->setSessionID($sessionID);
                    $session->send(new WelcomeMessage($sessionID, []));
                    return true;
                }
            }
        }
        return false;
    }

    public function processAuthenticateMessage(SessionInterface $session, AuthenticateMessage $message): bool
    {
        return false;
    }
}