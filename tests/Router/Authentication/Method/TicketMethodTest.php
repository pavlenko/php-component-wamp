<?php

namespace PE\Component\WAMP\Tests\Router\Authentication\Method;

use PE\Component\WAMP\Client\Authentication\Method\TicketMethod;
use PE\Component\WAMP\Client\Session\SessionInterface;
use PE\Component\WAMP\Message\AuthenticateMessage;
use PE\Component\WAMP\Message\ChallengeMessage;
use PHPUnit\Framework\TestCase;

final class TicketMethodTest extends TestCase
{
    public function testProcessChallengeMessage()
    {
        $message = new ChallengeMessage('ticket', []);
        $session = $this->createMock(SessionInterface::class);
        $session->expects(self::once())->method('send')->with(self::isInstanceOf(AuthenticateMessage::class));

        $method = new TicketMethod('abc');
        $method->processChallengeMessage($session, $message);
    }
}