<?php

namespace PE\Component\WAMP\Router\Session;

use PE\Component\WAMP\ErrorURI;
use PE\Component\WAMP\Message\GoodbyeMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\WelcomeMessage;
use PE\Component\WAMP\Router\Event\Events;
use PE\Component\WAMP\Router\Event\MessageEvent;
use PE\Component\WAMP\Router\RouterModuleInterface;
use PE\Component\WAMP\Session;
use PE\Component\WAMP\Util;

class SessionModule implements RouterModuleInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::MESSAGE_RECEIVED => 'onMessageReceived'
        ];
    }

    /**
     * @param MessageEvent $event
     */
    public function onMessageReceived(MessageEvent $event)
    {
        $session = $event->getSession();
        $message = $event->getMessage();

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