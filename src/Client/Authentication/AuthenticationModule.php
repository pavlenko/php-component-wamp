<?php

namespace PE\Component\WAMP\Client\Authentication;

use PE\Component\WAMP\Client\Authentication\Method\MethodInterface;
use PE\Component\WAMP\Client\Client;
use PE\Component\WAMP\Client\ClientModuleInterface;
use PE\Component\WAMP\Client\Session;
use PE\Component\WAMP\Message\AuthenticateMessage;
use PE\Component\WAMP\Message\ChallengeMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\Message;

class AuthenticationModule implements ClientModuleInterface
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
    public function subscribe(Client $client)
    {
        $client->on(Client::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
        $client->on(Client::EVENT_MESSAGE_SEND, [$this, 'onMessageSend']);
    }

    /**
     * @inheritDoc
     */
    public function unsubscribe(Client $client)
    {
        $client->off(Client::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
        $client->off(Client::EVENT_MESSAGE_SEND, [$this, 'onMessageSend']);
    }

    /**
     * @param Message $message
     * @param Session $session
     */
    public function onMessageReceived(Message $message, Session $session)
    {
        switch (true) {
            case ($message instanceof ChallengeMessage):
                $this->processChallengeMessage($session, $message);
                break;
        }
    }

    /**
     * @param Message $message
     */
    public function onMessageSend(Message $message)
    {
        if ($message instanceof HelloMessage) {
            $methods = array_map(function (MethodInterface $method) {
                return $method->getName();
            }, $this->methods);

            $message->setDetail('authmethods', $methods);
        }
    }

    /**
     * @param Session          $session
     * @param ChallengeMessage $message
     */
    private function processChallengeMessage(Session $session, ChallengeMessage $message)
    {
        $signature = 123456789;

        $session->send(new AuthenticateMessage($signature, []));
    }
}