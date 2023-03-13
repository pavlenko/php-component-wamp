<?php

namespace PE\Component\WAMP\Tests\Router\Authentication\Method;

use PE\Component\WAMP\Message\AuthenticateMessage;
use PE\Component\WAMP\Message\ChallengeMessage;
use PE\Component\WAMP\Message\ErrorMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\WelcomeMessage;
use PE\Component\WAMP\Router\Authentication\Method\TicketMethod;
use PE\Component\WAMP\Router\Session\SessionInterface;
use PE\Component\WAMP\Tests\Router\Session\SessionStub;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class TicketMethodTest extends TestCase
{
    /**
     * @return SessionInterface|MockObject
     */
    protected function createSessionMock()
    {
        return $this->getMockForAbstractClass(SessionStub::class, [], '', false, true, true, ['send']);
    }

    public function testProcessHelloMessage_no_methods()
    {
        $message = new HelloMessage('r', []);
        $session = $this->createSessionMock();

        $method = new TicketMethod([]);
        self::assertFalse($method->processHelloMessage($session, $message));
    }

    public function testProcessHelloMessage_no_tickets()
    {
        $message = new HelloMessage('r', ['authmethods' => ['ticket']]);
        $session = $this->createSessionMock();

        $method = new TicketMethod([]);
        self::assertFalse($method->processHelloMessage($session, $message));
    }

    public function testProcessHelloMessage_pass()
    {
        $message = new HelloMessage('r', ['authmethods' => ['ticket']]);
        $session = $this->createSessionMock();
        $session->expects(self::once())->method('send')->with(
            self::callback(fn(ChallengeMessage $m) => 'ticket' === $m->getAuthenticationMethod())
        );

        $method = new TicketMethod(['ticket']);
        self::assertTrue($method->processHelloMessage($session, $message));
    }

    public function testProcessAuthenticateMessage_skip()
    {
        $message = new AuthenticateMessage('sig', []);
        $session = $this->createSessionMock();

        $method = new TicketMethod(['ticket']);
        self::assertFalse($method->processAuthenticateMessage($session, $message));
    }

    public function testProcessAuthenticateMessage_fail()
    {
        $message = new AuthenticateMessage('sig', []);
        $session = $this->createSessionMock();
        $session->expects(self::once())->method('send')->with(
            self::callback(fn(ErrorMessage $m) => Message::ERROR_AUTHORIZATION_FAILED === $m->getErrorURI())
        );

        $session->authMethod = 'ticket';

        $method = new TicketMethod(['ticket']);
        self::assertTrue($method->processAuthenticateMessage($session, $message));
    }

    public function testProcessAuthenticateMessage_pass()
    {
        $message = new AuthenticateMessage('ticket', []);
        $session = $this->createSessionMock();
        $session->expects(self::once())->method('send')->with(self::isInstanceOf(WelcomeMessage::class));

        $session->authMethod = 'ticket';

        $method = new TicketMethod(['ticket']);
        self::assertTrue($method->processAuthenticateMessage($session, $message));
    }
}