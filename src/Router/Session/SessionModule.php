<?php

namespace PE\Component\WAMP\Router\Session;

use PE\Component\WAMP\ErrorURI;
use PE\Component\WAMP\Message\GoodbyeMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\WelcomeMessage;
use PE\Component\WAMP\Router\Router;
use PE\Component\WAMP\Router\RouterModuleInterface;
use PE\Component\WAMP\Session;
use PE\Component\WAMP\Util;

final class SessionModule implements RouterModuleInterface
{
    /**
     * @inheritDoc
     */
    public function attach(Router $router): void
    {
        $router->on(Router::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived'], 0);
    }

    /**
     * @inheritDoc
     */
    public function detach(Router $router): void
    {
        $router->off(Router::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
    }

    /**
     * @param Message $message
     * @param Session $session
     */
    public function onMessageReceived(Message $message, Session $session): void
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
     * @param Session      $session
     * @param HelloMessage $message
     */
    private function processHelloMessage(Session $session, HelloMessage $message): void
    {
        $sessionID = Util::generateID();

        $session->setSessionID($sessionID);
        $session->send(new WelcomeMessage($sessionID, []));
    }

    /**
     * @param Session        $session
     * @param GoodbyeMessage $message
     */
    private function processGoodbyeMessage(Session $session, GoodbyeMessage $message): void
    {
        $session->send(new GoodbyeMessage([], ErrorURI::_GOODBYE_AND_OUT));
        $session->shutdown();
    }
}
