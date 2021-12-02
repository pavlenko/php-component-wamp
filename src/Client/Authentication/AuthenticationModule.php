<?php

namespace PE\Component\WAMP\Client\Authentication;

use PE\Component\WAMP\Client\Authentication\Method\MethodInterface;
use PE\Component\WAMP\Client\Client;
use PE\Component\WAMP\Client\ClientModuleInterface;
use PE\Component\WAMP\Client\Session;
use PE\Component\WAMP\Message\ChallengeMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\Message;

final class AuthenticationModule implements ClientModuleInterface
{
    /**
     * @var MethodInterface[]
     */
    private array $methods = [];

    /**
     * @param MethodInterface $method
     */
    public function addMethod(MethodInterface $method): void
    {
        $this->methods[] = $method;
    }

    /**
     * @inheritDoc
     */
    public function subscribe(Client $client): void
    {
        $client->on(Client::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
        $client->on(Client::EVENT_MESSAGE_SEND, [$this, 'onMessageSend']);
    }

    /**
     * @inheritDoc
     */
    public function unsubscribe(Client $client): void
    {
        $client->off(Client::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
        $client->off(Client::EVENT_MESSAGE_SEND, [$this, 'onMessageSend']);
    }

    /**
     * @param Message $message
     * @param Session $session
     */
    public function onMessageReceived(Message $message, Session $session): void
    {
        if ($message instanceof ChallengeMessage) {
            $this->processChallengeMessage($session, $message);
        }
    }

    /**
     * @param Message $message
     * @param Session $session
     */
    public function onMessageSend(Message $message, Session $session): void
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

    /**
     * @param Session          $session
     * @param ChallengeMessage $message
     */
    private function processChallengeMessage(Session $session, ChallengeMessage $message): void
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
