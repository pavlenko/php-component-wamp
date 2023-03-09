<?php

namespace PE\Component\WAMP\Router\Authentication;

use PE\Component\WAMP\Message\AuthenticateMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\MessageFactory;
use PE\Component\WAMP\Router\Authentication\Method\MethodInterface;
use PE\Component\WAMP\Router\Router;
use PE\Component\WAMP\Router\RouterModuleInterface;
use PE\Component\WAMP\Router\SessionInterface;
use PE\Component\WAMP\Util\EventsInterface;

final class AuthenticationModule implements RouterModuleInterface
{
    /**
     * @var MethodInterface[]
     */
    private array $methods = [];

    public function addMethod(MethodInterface $method): void
    {
        $this->methods[] = $method;
    }

    public function attach(EventsInterface $events): void
    {
        $events->attach(Router::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived'], -10);
    }

    public function detach(EventsInterface $events): void
    {
        $events->detach(Router::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
    }

    public function onMessageReceived(Message $message, SessionInterface $session): bool
    {
        switch (true) {
            case ($message instanceof HelloMessage):
                $this->processHelloMessage($session, $message);
                return false;
            case ($message instanceof AuthenticateMessage):
                $this->processAuthenticateMessage($session, $message);
                break;
        }
        return true;
    }

    private function processHelloMessage(SessionInterface $session, HelloMessage $message): void
    {
        $methods = (array) $message->getDetail('authmethods', []);

        foreach ($this->methods as $method) {
            //TODO add supports() to methods instead of check by name here
            if (in_array($method->getName(), $methods, true) && $method->processHelloMessage($session, $message)) {
                $session->setAuthMethod($method->getName());
                return;
            }
        }

        if (count($this->methods)) {
            $session->send(MessageFactory::createErrorMessageFromMessage($message, Message::ERROR_NOT_AUTHORIZED));
        }
    }

    /**
     * @param SessionInterface $session
     * @param AuthenticateMessage $message
     */
    private function processAuthenticateMessage(SessionInterface $session, AuthenticateMessage $message): void
    {
        foreach ($this->methods as $method) {
            //TODO same as above but check selected method, maybe move check to method classes
            if ($method->getName() === $session->getAuthMethod()) {
                $method->processAuthenticateMessage($session, $message);
                return;
            }
        }
    }
}
