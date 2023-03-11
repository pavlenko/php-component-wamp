<?php

namespace PE\Component\WAMP\Router\Authentication;

use PE\Component\WAMP\Message\AuthenticateMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Router\Authentication\Method\AnonymousMethod;
use PE\Component\WAMP\Router\Authentication\Method\MethodInterface;
use PE\Component\WAMP\Router\Router;
use PE\Component\WAMP\Router\RouterModuleInterface;
use PE\Component\WAMP\Router\Session\SessionInterface;
use PE\Component\WAMP\Util\EventsInterface;

final class AuthenticationModule implements RouterModuleInterface
{
    /**
     * @var MethodInterface[]
     */
    private array $methods;

    public function __construct(MethodInterface ...$methods)
    {
        $this->methods = !empty($methods) ? $methods : [new AnonymousMethod()];
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
        foreach ($this->methods as $method) {
            if ($method->processHelloMessage($session, $message)) {
                return;
            }
        }
    }

    /**
     * @param SessionInterface $session
     * @param AuthenticateMessage $message
     */
    private function processAuthenticateMessage(SessionInterface $session, AuthenticateMessage $message): void
    {
        foreach ($this->methods as $method) {
            if ($method->processAuthenticateMessage($session, $message)) {
                return;
            }
        }
    }
}
