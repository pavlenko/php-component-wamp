<?php

namespace PE\Component\WAMP\Client\Session;

use PE\Component\WAMP\Client\ClientInterface;
use PE\Component\WAMP\Client\ClientModuleInterface;
use PE\Component\WAMP\Message\AbortMessage;
use PE\Component\WAMP\Message\GoodbyeMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\WelcomeMessage;
use PE\Component\WAMP\Util\EventsInterface;

final class SessionModule implements ClientModuleInterface
{
    private ?EventsInterface $events = null;

    public function attach(EventsInterface $events): void
    {
        $this->events = $events;
        $this->events->attach(ClientInterface::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
    }

    public function detach(EventsInterface $events): void
    {
        $this->events = $events;
        $this->events->detach(ClientInterface::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
    }

    public function onMessageReceived(Message $message, SessionInterface $session): void
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

    private function processWelcomeMessage(SessionInterface $session, WelcomeMessage $message): void
    {
        if (0 !== $session->getSessionID()) {
            $session->send(new AbortMessage(
                ['message' => 'Received WELCOME message after session was established.'],
                Message::ERROR_PROTOCOL_VIOLATION
            ));
            return;
        }

        $session->setSessionID($message->getSessionId());
        if ($this->events) {
            $this->events->trigger(ClientInterface::EVENT_SESSION_ESTABLISHED, $session);
        }
    }

    private function processGoodbyeMessage(SessionInterface $session): void
    {
        $session->send(new GoodbyeMessage([], Message::ERROR_GOODBYE_AND_OUT));
        $session->shutdown();
    }

    private function processAbortMessage(SessionInterface $session): void
    {
        $session->shutdown();
    }
}