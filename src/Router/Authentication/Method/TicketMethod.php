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
    private string $ticket;

    public function __construct(string $ticket)
    {
        $this->ticket = $ticket;
    }

    public function getName(): string
    {
        return 'ticket';
    }

    public function processHelloMessage(SessionInterface $session, HelloMessage $message): void
    {
        $session->send(new ChallengeMessage($this->getName(), []));
    }

    public function processAuthenticateMessage(SessionInterface $session, AuthenticateMessage $message): void
    {
        if ($message->getSignature() === $this->ticket) {
            $sessionID = Util::generateID();

            $session->setSessionID($sessionID);
            $session->send(new WelcomeMessage($sessionID, []));
        } else {
            $session->send(MessageFactory::createErrorMessageFromMessage($message, Message::ERROR_NOT_AUTHORIZED));
        }
    }
}

