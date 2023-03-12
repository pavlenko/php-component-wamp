<?php

namespace PE\Component\WAMP\Tests\Router\Authentication\Method;

use PE\Component\WAMP\Message\AuthenticateMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\WelcomeMessage;
use PE\Component\WAMP\Router\Authentication\Method\AnonymousMethod;
use PE\Component\WAMP\Router\Session\SessionInterface;
use PE\Component\WAMP\Tests\Router\Session\SessionStub;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AnonymousMethodTest extends TestCase
{
    public function testProcessHelloMessageSkip()
    {
        $message = new HelloMessage(1, []);
        $session = $this->createMock(SessionInterface::class);

        self::assertFalse((new AnonymousMethod())->processHelloMessage($session, $message));
    }

    public function testProcessHelloMessagePass()
    {
        $message = new HelloMessage(1, ['authmethods' => ['anonymous']]);
        $session = $this->createMock(SessionInterface::class);
        $session->expects(self::once())->method('send')->with(self::isInstanceOf(WelcomeMessage::class));

        self::assertTrue((new AnonymousMethod())->processHelloMessage($session, $message));
    }

    public function testProcessAuthenticateMessage()
    {
        /* @var $session SessionInterface|MockObject */
        $message = new AuthenticateMessage('sig', []);
        $session = $this->getMockForAbstractClass(SessionStub::class, [], '', false, true, true, ['send']);

        self::assertFalse((new AnonymousMethod())->processAuthenticateMessage($session, $message));
        $session->authMethod = 'anonymous';
        self::assertTrue((new AnonymousMethod())->processAuthenticateMessage($session, $message));
    }
}