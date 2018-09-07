<?php

namespace PE\Component\WAMP\Client\Session;

use PE\Component\WAMP\Client\Client;
use PE\Component\WAMP\Client\ClientModuleInterface;
use PE\Component\WAMP\ErrorURI;
use PE\Component\WAMP\Message\AbortMessage;
use PE\Component\WAMP\Message\GoodbyeMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\WelcomeMessage;
use PE\Component\WAMP\Session;

class SessionModule implements ClientModuleInterface
{
    /**
     * @inheritDoc
     */
    public function subscribe(Client $client)
    {
        $client->on(Client::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
    }

    /**
     * @inheritDoc
     */
    public function unsubscribe(Client $client)
    {
        $client->off(Client::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
    }

    /**
     * @param Message $message
     * @param Session $session
     */
    public function onMessageReceived(Message $message, Session $session)
    {
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