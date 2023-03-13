<?php

namespace PE\Component\WAMP\Tests\Router\Session;

use PE\Component\WAMP\Message\AbortMessage;
use PE\Component\WAMP\Message\GoodbyeMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\WelcomeMessage;
use PE\Component\WAMP\Router\Router;
use PE\Component\WAMP\Router\RouterInterface;
use PE\Component\WAMP\Router\Session\SessionInterface;
use PE\Component\WAMP\Router\Session\SessionModule;
use PE\Component\WAMP\Util\EventsInterface;
use PHPUnit\Framework\TestCase;

final class SessionModuleTest extends TestCase
{
    public function testAttach()
    {
        $module = new SessionModule();

        $events = $this->createMock(EventsInterface::class);
        $events->expects(self::once())->method('attach')->with(
            Router::EVENT_MESSAGE_RECEIVED,
            [$module, 'onMessageReceived']
        );

        $module->attach($events);
    }

    public function testDetach()
    {
        $module = new SessionModule();

        $events = $this->createMock(EventsInterface::class);
        $events->expects(self::once())->method('detach')->with(
            Router::EVENT_MESSAGE_RECEIVED,
            [$module, 'onMessageReceived']
        );

        $module->detach($events);
    }

    public function testOnMessageReceivedHELLO_no_realm()
    {
        $module = new SessionModule();
        $router = $this->createMock(RouterInterface::class);
        $router->method('getRealms')->willReturn(['foo']);

        $session = $this->createMock(SessionInterface::class);
        $session->expects(self::once())->method('send')->with(self::isInstanceOf(AbortMessage::class));

        $module->onMessageReceived(new HelloMessage('realm', []), $session, $router);
    }

    public function testOnMessageReceivedHELLO_duplicate()
    {
        $module = new SessionModule();
        $router = $this->createMock(RouterInterface::class);

        $session = $this->createMock(SessionInterface::class);
        $session->expects(self::once())->method('getSessionID')->willReturn(1);
        $session->expects(self::once())->method('send')->with(self::isInstanceOf(AbortMessage::class));

        $module->onMessageReceived(new HelloMessage('realm', []), $session, $router);
    }

    public function testOnMessageReceivedHELLO()
    {
        $module = new SessionModule();
        $router = $this->createMock(RouterInterface::class);

        $session = $this->createMock(SessionInterface::class);
        $session->expects(self::once())->method('setSessionID');
        $session->expects(self::once())->method('send')->with(self::isInstanceOf(WelcomeMessage::class));

        $module->onMessageReceived(new HelloMessage('realm', []), $session, $router);
    }

    public function testOnMessageReceivedGOODBYE()
    {
        $module = new SessionModule();
        $router = $this->createMock(RouterInterface::class);

        $session = $this->createMock(SessionInterface::class);
        $session->expects(self::once())->method('send')->with(self::isInstanceOf(GoodbyeMessage::class));
        $session->expects(self::once())->method('shutdown');

        $module->onMessageReceived(new GoodbyeMessage([], 'foo'), $session, $router);
    }
}
