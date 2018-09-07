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

class SessionModule implements RouterModuleInterface
{
    /**
     * @inheritDoc
     */
    public function subscribe(Router $router)
    {
        $router->on(Router::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived'], 0);
    }

    /**
     * @inheritDoc
     */
    public function unsubscribe(Router $router)
    {
        $router->off(Router::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
    }

    /**
     * @param Message $message
     * @param Session $session
     */
    public function onMessageReceived(Message $message, Session $session)
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
    private function processHelloMessage(Session $session, HelloMessage $message)
    {
        $sessionID = Util::generateID();

        $session->setSessionID($sessionID);
        $session->send(new WelcomeMessage($sessionID, []));
    }

    /**
     * @param Session        $session
     * @param GoodbyeMessage $message
     */
    private function processGoodbyeMessage(Session $session, GoodbyeMessage $message)
    {
        $session->send(new GoodbyeMessage([], ErrorURI::_GOODBYE_AND_OUT));
        $session->shutdown();
    }
}