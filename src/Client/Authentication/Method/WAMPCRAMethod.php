<?php

namespace PE\Component\WAMP\Client\Authentication\Method;

use PE\Component\WAMP\Client\Session\SessionInterface;
use PE\Component\WAMP\Message\AuthenticateMessage;
use PE\Component\WAMP\Message\ChallengeMessage;
use PE\Component\WAMP\Message\HelloMessage;

final class WAMPCRAMethod implements MethodInterface
{
    private string $secret;
    private string $authID;

    public function __construct(string $secret, string $authID)
    {
        $this->secret = $secret;
        $this->authID = $authID;
    }

    public function getName(): string
    {
        return 'wampcra';
    }

    public function processHelloMessage(SessionInterface $session, HelloMessage $message): void
    {
        $message->setDetail('authid', $this->authID);
    }

    public function processChallengeMessage(SessionInterface $session, ChallengeMessage $message): void
    {
        $extra = $message->getExtra();

        if (!empty($extra['challenge'])) {
            $signature = hash_hmac('sha256', (string) $extra['challenge'], $this->secret);

            $session->send(new AuthenticateMessage($signature, []));
        }
    }
}
