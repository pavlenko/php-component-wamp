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

final class SessionModule implements ClientModuleInterface
{
    public function attach(Client $client): void
    {
        $client->on(Client::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
    }

    public function detach(Client $client): void
    {
        $client->off(Client::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
    }

    public function onMessageReceived(Message $message, Session $session): void
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

    private function processWelcomeMessage(Session $session, WelcomeMessage $message): void
    {
        $session->setSessionID($message->getSessionId());
    }

    private function processGoodbyeMessage(Session $session): void
    {
        $session->send(new GoodbyeMessage([], ErrorURI::_GOODBYE_AND_OUT));
        $session->shutdown();
    }

    private function processAbortMessage(Session $session): void
    {
        $session->shutdown();
    }
}