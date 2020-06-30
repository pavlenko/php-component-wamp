<?php

namespace PE\Component\WAMP\Router\Authentication\Method;

use PE\Component\WAMP\Message\AuthenticateMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\WelcomeMessage;
use PE\Component\WAMP\Router\Session;
use PE\Component\WAMP\Util;

class AnonymousMethod implements MethodInterface
{
    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'anonymous';
    }

    /**
     * @inheritDoc
     */
    public function processHelloMessage(Session $session, HelloMessage $message)
    {
        $sessionID = Util::generateID();

        $session->setSessionID($sessionID);
        $session->send(new WelcomeMessage($sessionID, []));
    }

    /**
     * @inheritDoc
     */
    public function processAuthenticateMessage(Session $session, AuthenticateMessage $message)
    {
        // DO NOTHING
    }
}
