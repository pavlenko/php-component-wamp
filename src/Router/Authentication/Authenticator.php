<?php

namespace PE\Component\WAMP\Router\Authentication;

use PE\Component\WAMP\Message\AbortMessage;
use PE\Component\WAMP\Message\AuthenticateMessage;
use PE\Component\WAMP\Message\ChallengeMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\WelcomeMessage;
use PE\Component\WAMP\Router\Event\MessageEvent;
use PE\Component\WAMP\Router\Session;

class Authenticator
{
    public function onMessageReceived(MessageEvent $event)
    {
        $session = $event->getSession();
        $message = $event->getMessage();

        switch (true) {
            case ($message instanceof HelloMessage):
                $this->processHelloMessage($session, $message);
                break;
            case ($message instanceof AuthenticateMessage):
                $this->processAuthenticateMessage($session, $message);
                break;
        }
    }

    private function processHelloMessage(Session $session, HelloMessage $message)
    {
        $session->send(new WelcomeMessage('session_id', []));// If challenge not required
        $session->send(new ChallengeMessage('auth_method_name', []));// Else
    }

    private function processAuthenticateMessage(Session $session, AuthenticateMessage $message)
    {
        $session->send(new WelcomeMessage('session_id', []));// If authentication success
        $session->send(new ChallengeMessage('auth_method_name', []));// Else if two factor used
        $session->send(new AbortMessage([], 'response_uri'));// Else
    }
}