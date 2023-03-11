<?php

namespace PE\Component\WAMP\Router\Authentication\Method;

use PE\Component\WAMP\Message\AbortMessage;
use PE\Component\WAMP\Message\AuthenticateMessage;
use PE\Component\WAMP\Message\ChallengeMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\WelcomeMessage;
use PE\Component\WAMP\Router\Session\SessionInterface;
use PE\Component\WAMP\Util;

final class WAMPCRAMethod implements MethodInterface
{
    private string $secret;
    private string $provider;

    private array $users;

    public function __construct(string $secret, string $provider, array $users)
    {
        $this->secret   = $secret;
        $this->provider = $provider;
        $this->users    = $users;//TODO collection of user objects
    }

    public function processHelloMessage(SessionInterface $session, HelloMessage $message): bool
    {
        $methods = (array) $message->getDetail('authmethods');
        $authID  = $message->getDetail('authid');

        if (in_array('wampcra', $methods) && !empty($authID) && array_key_exists($authID, $this->users)) {
            $sessionID = Util::generateID();

            $session->challenge = json_encode([
                'authid'       => $authID,
                'authrole'     => $this->users[$authID]['role'],
                'authmethod'   => 'wampcra',
                'authprovider' => $this->provider,
                'nonce'        => md5((string) mt_rand()),
                'timestamp'    => date(DATE_ATOM),
                'session'      => $sessionID,
            ]);

            $session->setSessionID($sessionID);
            $session->send(new ChallengeMessage('wampcra', ['challenge' => $session->challenge]));
            $session->authMethod = 'wampcra';
            return true;
        }
        return false;
    }

    public function processAuthenticateMessage(SessionInterface $session, AuthenticateMessage $message): bool
    {
        if ('wampcra' !== $session->authMethod) {
            return false;
        }

        $challenge = $session->challenge;
        $signature = $message->getSignature();

        if (hash_equals(hash_hmac('sha512', $challenge, $this->secret), $signature)) {
            $data = json_decode($challenge, true);

            unset($data['nonce'], $data['timestamp'], $data['session']);

            $session->send(new WelcomeMessage($session->getSessionID(), $data));
        } else {
            $session->send(new AbortMessage([], Message::ERROR_AUTHORIZATION_FAILED));
        }
        return true;
    }
}
