<?php

namespace PE\Component\WAMP\Tests\Router\Authentication;

use PE\Component\WAMP\Message\AuthenticateMessage;
use PE\Component\WAMP\Message\ErrorMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Router\Authentication\AuthenticationModule;
use PE\Component\WAMP\Router\Authentication\Method\MethodInterface;
use PE\Component\WAMP\Router\Router;
use PE\Component\WAMP\Router\Session\SessionInterface;
use PE\Component\WAMP\Util\EventsInterface;
use PHPUnit\Framework\TestCase;

final class AuthenticationModuleTest extends TestCase
{
    public function testAttach()
    {
        $module = new AuthenticationModule();

        $events = $this->createMock(EventsInterface::class);
        $events->expects(self::once())->method('attach')->with(
            Router::EVENT_MESSAGE_RECEIVED, [$module, 'onMessageReceived'], -10
        );

        $module->attach($events);
    }

    public function testDetach()
    {
        $module = new AuthenticationModule();

        $events = $this->createMock(EventsInterface::class);
        $events->expects(self::once())->method('detach')->with(
            Router::EVENT_MESSAGE_RECEIVED, [$module, 'onMessageReceived']
        );

        $module->detach($events);
    }

    public function testOnMessageReceivedHELLO()
    {
        $message = new HelloMessage('realm', []);

        $session = $this->createMock(SessionInterface::class);

        $method = $this->createMock(MethodInterface::class);
        $method->expects(self::once())->method('processHelloMessage')->with($session, $message)->willReturn(true);

        $module = new AuthenticationModule($method);
        $module->onMessageReceived($message, $session);
    }

    public function testOnMessageReceivedAUTHENTICATE()
    {
        $message = new AuthenticateMessage('sig', []);

        $session = $this->createMock(SessionInterface::class);

        $method = $this->createMock(MethodInterface::class);
        $method->expects(self::once())->method('processAuthenticateMessage')->with($session, $message)->willReturn(true);

        $module = new AuthenticationModule($method);
        $module->onMessageReceived($message, $session);
    }
}