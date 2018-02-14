<?php

namespace PE\Component\WAMP\Client\Session;

use PE\Component\WAMP\Client\ClientModuleInterface;
use PE\Component\WAMP\Client\Event\Events;
use PE\Component\WAMP\Client\Event\MessageEvent;
use PE\Component\WAMP\ErrorURI;
use PE\Component\WAMP\Message\AbortMessage;
use PE\Component\WAMP\Message\GoodbyeMessage;
use PE\Component\WAMP\Message\WelcomeMessage;
use PE\Component\WAMP\Session;

class SessionModule implements ClientModuleInterface
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
            case ($message instanceof WelcomeMessage):
                $this->processWelcomeMessage($session, $message);
                break;
            case ($message instanceof GoodbyeMessage):
                $this->processGoodbyeMessage($session);
                break;
            case ($message instanceof AbortMessage):
                $this->processAbortMessage($session);
                break;
        }
    }

    /**
     * @param Session        $session
     * @param WelcomeMessage $message
     */
    private function processWelcomeMessage(Session $session, WelcomeMessage $message)
    {
        $session->setSessionID($message->getSessionId());
    }

    /**
     * @param Session $session
     */
    private function processGoodbyeMessage(Session $session)
    {
        $session->send(new GoodbyeMessage([], ErrorURI::_GOODBYE_AND_OUT));
        $session->shutdown();
    }

    /**
     * @param Session $session
     */
    private function processAbortMessage(Session $session)
    {
        $session->shutdown();
    }
}