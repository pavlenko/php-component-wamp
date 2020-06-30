<?php

namespace PE\Component\WAMP\Router\Authentication;

use PE\Component\WAMP\ErrorURI;
use PE\Component\WAMP\Message\AuthenticateMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\MessageFactory;
use PE\Component\WAMP\Router\Authentication\Method\MethodInterface;
use PE\Component\WAMP\Router\Router;
use PE\Component\WAMP\Router\RouterModuleInterface;
use PE\Component\WAMP\Router\Session;

class AuthenticationModule implements RouterModuleInterface
{
    /**
     * @var MethodInterface[]
     */
    private $methods = [];

    /**
     * @param MethodInterface $method
     */
    public function addMethod(MethodInterface $method)
    {
        $this->methods[] = $method;
    }

    /**
     * @inheritDoc
     */
    public function subscribe(Router $router)
    {
        $router->on(Router::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived'], 10);
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
            case ($message instanceof AuthenticateMessage):
                $this->processAuthenticateMessage($session, $message);
                break;
        }
    }

    /**
     * @param Session      $session
     * @param HelloMessage $message
     */
    private function processHelloMessage(Session $session, HelloMessage $message)
    {
        $methods = (array) $message->getDetail('authmethods', []);

        foreach ($this->methods as $method) {
            if (in_array($method->getName(), $methods, true)) {
                $method->processHelloMessage($session, $message);
                $session->setAuthMethod($method->getName());
                return;
            }
        }

        if (count($this->methods)) {
            $session->send(MessageFactory::createErrorMessageFromMessage($message, ErrorURI::_NOT_AUTHORIZED));
        }
    }

    /**
     * @param Session             $session
     * @param AuthenticateMessage $message
     */
    private function processAuthenticateMessage(Session $session, AuthenticateMessage $message)
    {
        foreach ($this->methods as $method) {
            if ($method->getName() === $session->getAuthMethod()) {
                $method->processAuthenticateMessage($session, $message);
                return;
            }
        }
    }
}
