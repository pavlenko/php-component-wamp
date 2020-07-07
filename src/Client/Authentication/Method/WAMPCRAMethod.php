<?php

namespace PE\Component\WAMP\Client\Authentication\Method;

use PE\Component\WAMP\Client\Session;
use PE\Component\WAMP\Message\AuthenticateMessage;
use PE\Component\WAMP\Message\ChallengeMessage;
use PE\Component\WAMP\Message\HelloMessage;

class WAMPCRAMethod implements MethodInterface
{
    private $secret;
    private $authID;

    /**
     * @param $secret
     * @param $authID
     */
    public function __construct($secret, $authID)
    {
        $this->secret = $secret;
        $this->authID = $authID;
    }

    public function getName()
    {
        return 'wampcra';
    }

    public function processHelloMessage(Session $session, HelloMessage $message)
    {
        $message->setDetail('authid', $this->authID);
    }

    public function processChallengeMessage(Session $session, ChallengeMessage $message)
    {
        $extra = $message->getExtra();

        if (!empty($extra['challenge'])) {
            $signature = hash_hmac('sha256', (string) $extra['challenge'], $this->secret);

            $session->send(new AuthenticateMessage($signature, []));
        }
    }
}
