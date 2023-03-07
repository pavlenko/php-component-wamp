<?php

namespace PE\Component\WAMP\Router\Session;

use PE\Component\WAMP\ErrorURI;
use PE\Component\WAMP\Message\GoodbyeMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\WelcomeMessage;
use PE\Component\WAMP\Router\Router;
use PE\Component\WAMP\Router\RouterModuleInterface;
use PE\Component\WAMP\Router\SessionInterface;
use PE\Component\WAMP\Util;
use PE\Component\WAMP\Util\EventsInterface;

final class SessionModule implements RouterModuleInterface
{
    /**
     * @inheritDoc
     */
    public function attach(EventsInterface $events): void
    {
        $events->attach(Router::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
    }

    /**
     * @inheritDoc
     */
    public function detach(EventsInterface $events): void
    {
        $events->detach(Router::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
    }

    /**
     * @param Message $message
     * @param SessionInterface $session
     */
    public function onMessageReceived(Message $message, SessionInterface $session): void
    {
        switch (true) {
            case ($message instanceof HelloMessage):
                $this->processHelloMessage($session, $message);
                break;
            case ($message instanceof GoodbyeMessage):
                $this->processGoodbyeMessage($session, $message);
                break;
        }
    }

    /**
     * @param SessionInterface $session
     */
    private function processHelloMessage(SessionInterface $session/*, HelloMessage $message*/): void
    {
        $sessionID = Util::generateID();

        $session->setSessionID($sessionID);
        $session->send(new WelcomeMessage($sessionID, []));
    }

    /**
     * @param SessionInterface $session
     */
    private function processGoodbyeMessage(SessionInterface $session/*, GoodbyeMessage $message*/): void
    {
        $session->send(new GoodbyeMessage([], ErrorURI::_GOODBYE_AND_OUT));
        $session->shutdown();
    }
}
