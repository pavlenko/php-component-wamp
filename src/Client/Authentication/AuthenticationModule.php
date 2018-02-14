<?php

namespace PE\Component\WAMP\Client\Authentication;

use PE\Component\WAMP\Client\Authentication\Method\MethodInterface;
use PE\Component\WAMP\Client\ClientModuleInterface;
use PE\Component\WAMP\Client\Event\Events;
use PE\Component\WAMP\Client\Event\MessageEvent;
use PE\Component\WAMP\Message\AuthenticateMessage;
use PE\Component\WAMP\Message\ChallengeMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Session;

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
    public static function getSubscribedEvents()
    {
        return [
            Events::MESSAGE_RECEIVED => 'onMessageReceived',
            Events::MESSAGE_SEND     => 'onMessageSend',
        ];
    }

    /**
     * @param MessageEvent $event
     */
    public function onMessageReceived(MessageEvent $event)
    {
        $session = $event->getSession();
        $message = $event->getMessage();

        switch (true) {
            case ($message instanceof ChallengeMessage):
                $this->processChallengeMessage($session, $message);
                break;
        }
    }

    /**
     * @param MessageEvent $event
     */
    public function onMessageSend(MessageEvent $event)
    {
        $message = $event->getMessage();

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