<?php

namespace PE\Component\WAMP\Router\Authentication\Method;

use PE\Component\WAMP\Message\AuthenticateMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\WelcomeMessage;
use PE\Component\WAMP\Router\SessionInterface;
use PE\Component\WAMP\Util;

final class AnonymousMethod implements MethodInterface
{
    public function processHelloMessage(SessionInterface $session, HelloMessage $message): bool
    {
        $methods = (array) $message->getDetail('authmethods');
        if (!in_array('anonymous', $methods)) {
            return false;
        }

        $sessionID = Util::generateID();

        $session->setSessionID($sessionID);
        $session->send(new WelcomeMessage($sessionID, []));
        return true;
    }

    public function processAuthenticateMessage(SessionInterface $session, AuthenticateMessage $message): bool
    {
        return 'anonymous' === $session->authMethod;
    }
}
