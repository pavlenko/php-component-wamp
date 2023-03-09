<?php

namespace PE\Component\WAMP\Router\Authentication\Method;

use PE\Component\WAMP\Message\AuthenticateMessage;
use PE\Component\WAMP\Message\ChallengeMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\MessageFactory;
use PE\Component\WAMP\Message\WelcomeMessage;
use PE\Component\WAMP\Router\SessionInterface;
use PE\Component\WAMP\Util;

final class TicketMethod implements MethodInterface
{
    /**
     * @var string[]
     */
    private array $tickets;

    public function __construct(array $tickets)
    {
        $this->tickets = $tickets;
    }

    public function processHelloMessage(SessionInterface $session, HelloMessage $message): bool
    {
        $methods = (array) $message->getDetail('authmethods');
        if (!in_array('ticket', $methods) || empty($this->tickets)) {
            return false;
        }

        $session->send(new ChallengeMessage('ticket', []));
        $session->authMethod = 'ticket';
        return true;
    }

    public function processAuthenticateMessage(SessionInterface $session, AuthenticateMessage $message): bool
    {
        if ('ticket' !== $session->authMethod) {
            return false;
        }
        if (in_array($message->getSignature(), $this->tickets)) {
            $sessionID = Util::generateID();

            $session->setSessionID($sessionID);
            $session->send(new WelcomeMessage($sessionID, []));
        } else {
            $session->send(MessageFactory::createErrorMessageFromMessage($message, Message::ERROR_NOT_AUTHORIZED));
        }
        return true;
    }
}

