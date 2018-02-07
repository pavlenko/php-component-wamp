<?php

namespace PE\Component\WAMP\Client\Authentication;

use PE\Component\WAMP\Client\Event\MessageEvent;
use PE\Component\WAMP\Client\Session;
use PE\Component\WAMP\Message\AuthenticateMessage;
use PE\Component\WAMP\Message\ChallengeMessage;

class Authenticator
{
    public function onMessageReceived(MessageEvent $event)
    {
        $session = $event->getSession();
        $message = $event->getMessage();

        switch (true) {
            case ($message instanceof ChallengeMessage):
                $this->processChallengeMessage($session, $message);
                break;
        }
    }

    public function processChallengeMessage(Session $session, ChallengeMessage $message)
    {
        $session->send(new AuthenticateMessage('123456789', []));
    }
}