<?php

namespace PE\Component\WAMP\Client\Authentication;

use PE\Component\WAMP\Client\Authentication\Method\MethodInterface;
use PE\Component\WAMP\Client\Client;
use PE\Component\WAMP\Client\ClientModuleInterface;
use PE\Component\WAMP\Client\SessionInterface;
use PE\Component\WAMP\Message\ChallengeMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Util\EventsInterface;

final class AuthenticationModule implements ClientModuleInterface
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
        $events->attach(Client::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived'], -10);
        $events->attach(Client::EVENT_MESSAGE_SEND, [$this, 'onMessageSend']);
    }

    /**
     * @inheritDoc
     */
    public function detach(EventsInterface $events): void
    {
        $events->detach(Client::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
        $events->detach(Client::EVENT_MESSAGE_SEND, [$this, 'onMessageSend']);
    }

    public function onMessageReceived(Message $message, SessionInterface $session): void
    {
        if ($message instanceof ChallengeMessage) {
            $this->processChallengeMessage($session, $message);
        }
    }

    public function onMessageSend(Message $message, SessionInterface $session): void
    {
        if ($message instanceof HelloMessage) {
            foreach ($this->methods as $method) {
                $method->processHelloMessage($session, $message);
            }

            $methods = array_map(static function (MethodInterface $method) {
                return $method->getName();
            }, $this->methods);

            $message->setDetail('authmethods', $methods);
        }
    }

    private function processChallengeMessage(SessionInterface $session, ChallengeMessage $message): void
    {
        foreach ($this->methods as $method) {
            if ($method->getName() === $message->getAuthenticationMethod()) {
                $method->processChallengeMessage($session, $message);
                return;
            }
        }

        throw new \LogicException('Unknown authentication method: ' . $message->getAuthenticationMethod());
    }
}
