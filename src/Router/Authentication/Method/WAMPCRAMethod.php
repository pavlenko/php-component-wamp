<?php

namespace PE\Component\WAMP\Router\Authentication\Method;

use PE\Component\WAMP\ErrorURI;
use PE\Component\WAMP\Message\AbortMessage;
use PE\Component\WAMP\Message\AuthenticateMessage;
use PE\Component\WAMP\Message\ChallengeMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\WelcomeMessage;
use PE\Component\WAMP\Router\Session;
use PE\Component\WAMP\Util;

class WAMPCRAMethod implements MethodInterface
{
    private $secret;
    private $provider;

    private $users;

    /**
     * @param string $secret
     * @param string $provider
     * @param array  $users
     */
    public function __construct($secret, $provider, array $users)
    {
        $this->secret   = $secret;
        $this->provider = $provider;
        $this->users    = $users;//TODO collection of user objects
    }

    public function getName()
    {
        return 'wampcra';
    }

    public function processHelloMessage(Session $session, HelloMessage $message)
    {
        $authID = $message->getDetail('authid');

        if (!empty($authID) && array_key_exists($authID, $this->users)) {
            $sessionID = Util::generateID();

            $session->challenge = json_encode([
                'authid'       => 'accepted auth id',
                'authrole'     => $this->users[$authID]['role'],
                'authmethod'   => $this->getName(),
                'authprovider' => $this->provider,
                'nonce'        => md5((string) mt_rand()),
                'timestamp'    => date(DATE_ATOM),
                'session'      => $sessionID,
            ]);

            $session->setSessionID($sessionID);
            $session->send(new ChallengeMessage($this->getName(), ['challenge' => $session->challenge]));
        }//TODO else
    }

    public function processAuthenticateMessage(Session $session, AuthenticateMessage $message)
    {
        $challenge = $session->challenge;
        $signature = $message->getSignature();

        if (hash_equals(hash_hmac('sha512', $challenge, $this->secret), $signature)) {
            $data = json_decode($challenge, true);

            unset($data['nonce'], $data['timestamp'], $data['session']);

            $session->send(new WelcomeMessage($session->getSessionID(), $data));
        } else {
            $session->send(new AbortMessage([], ErrorURI::_AUTHORIZATION_FAILED));
        }
    }
}
