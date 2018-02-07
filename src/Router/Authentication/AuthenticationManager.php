<?php

namespace PE\Component\WAMP\Router\Authentication;

use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Router\Event\MessageEvent;
use PE\Component\WAMP\Router\Session;

class AuthenticationManager
{
    /**
     * @var AuthenticatorInterface[]
     */
    private $authenticators = [];

    /**
     * @param AuthenticatorInterface $authenticator
     */
    public function addAuthenticator(AuthenticatorInterface $authenticator)
    {
        // Do not use index for allow same authenticator class used with different options
        $this->authenticators[] = $authenticator;
    }

    public function onMessageReceived(MessageEvent $event)
    {
        //TODO handle incoming message
        //TODO try each authenticator sequentially

        $session = $event->getSession();
        $message = $event->getMessage();

        switch (true) {
            case ($message instanceof HelloMessage):
                $this->processHelloMessage($session, $message);
                break;
        }
    }

    private function processHelloMessage(Session $session, HelloMessage $message)
    {
        //TODO process hello message for selected authenticator
        //TODO send CHALLENGE if needed or WELCOME if success or ABORT if error
    }
}