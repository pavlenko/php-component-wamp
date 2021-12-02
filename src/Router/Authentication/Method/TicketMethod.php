<?php

namespace PE\Component\WAMP\Router\Authentication\Method;

use PE\Component\WAMP\ErrorURI;
use PE\Component\WAMP\Message\AuthenticateMessage;
use PE\Component\WAMP\Message\ChallengeMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\MessageFactory;
use PE\Component\WAMP\Message\WelcomeMessage;
use PE\Component\WAMP\Router\Session;
use PE\Component\WAMP\Util;

final class TicketMethod implements MethodInterface
{
    /**
     * @var string
     */
    private string $ticket;

    /**
     * @param string $ticket
     */
    public function __construct(string $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'ticket';
    }

    /**
     * @inheritDoc
     */
    public function processHelloMessage(Session $session, HelloMessage $message): void
    {
        $session->send(new ChallengeMessage($this->getName(), []));
    }

    /**
     * @inheritDoc
     */
    public function processAuthenticateMessage(Session $session, AuthenticateMessage $message): void
    {
        if ($message->getSignature() === $this->ticket) {
            $sessionID = Util::generateID();

            $session->setSessionID($sessionID);
            $session->send(new WelcomeMessage($sessionID, []));
        } else {
            $session->send(MessageFactory::createErrorMessageFromMessage($message, ErrorURI::_NOT_AUTHORIZED));
        }
    }
}

