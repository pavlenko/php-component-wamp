<?php

namespace PE\Component\WAMP\Router\Authentication\Method;

use PE\Component\WAMP\Message\AuthenticateMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\WelcomeMessage;
use PE\Component\WAMP\Router\Session;
use PE\Component\WAMP\Util;

final class AnonymousMethod implements MethodInterface
{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'anonymous';
    }

    /**
     * @inheritDoc
     */
    public function processHelloMessage(Session $session, HelloMessage $message): void
    {
        $sessionID = Util::generateID();

        $session->setSessionID($sessionID);
        $session->send(new WelcomeMessage($sessionID, []));
    }

    /**
     * @inheritDoc
     */
    public function processAuthenticateMessage(Session $session, AuthenticateMessage $message): void
    {
        // DO NOTHING
    }
}
