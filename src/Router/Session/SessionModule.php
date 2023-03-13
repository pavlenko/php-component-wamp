<?php

namespace PE\Component\WAMP\Router\Session;

use PE\Component\WAMP\Message\AbortMessage;
use PE\Component\WAMP\Message\GoodbyeMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\WelcomeMessage;
use PE\Component\WAMP\Router\Router;
use PE\Component\WAMP\Router\RouterInterface;
use PE\Component\WAMP\Router\RouterModuleInterface;
use PE\Component\WAMP\Util;
use PE\Component\WAMP\Util\EventsInterface;

final class SessionModule implements RouterModuleInterface
{
    public function attach(EventsInterface $events): void
    {
        $events->attach(Router::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
    }

    public function detach(EventsInterface $events): void
    {
        $events->detach(Router::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
    }

    public function onMessageReceived(Message $message, SessionInterface $session, RouterInterface $router): void
    {
        switch (true) {
            case ($message instanceof HelloMessage):
                $this->processHelloMessage($session, $message, $router);
                break;
            case ($message instanceof GoodbyeMessage):
                $this->processGoodbyeMessage($session/*, $message*/);
                break;
        }
    }

    private function processHelloMessage(SessionInterface $session, HelloMessage $message, RouterInterface $router): void
    {
        if (!empty($router->getRealms()) && !in_array($message->getRealm(), $router->getRealms())) {
            $session->send(new AbortMessage(['message' => 'The realm does not exist.'], Message::ERROR_NO_SUCH_REALM));
            return;
        }

        if (0 !== $session->getSessionID()) {
            $session->send(new AbortMessage(
                ['message' => 'Received HELLO message after session was established.'],
                Message::ERROR_PROTOCOL_VIOLATION
            ));
            return;
        }

        $sessionID = Util::generateID();

        $session->setSessionID($sessionID);
        $session->send(new WelcomeMessage($sessionID, []));
    }

    private function processGoodbyeMessage(SessionInterface $session/*, GoodbyeMessage $message*/): void
    {
        $session->send(new GoodbyeMessage([], Message::ERROR_GOODBYE_AND_OUT));
        $session->shutdown();
    }
}
