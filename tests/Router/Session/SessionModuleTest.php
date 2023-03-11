<?php

namespace PE\Component\WAMP\Tests\Router\Session;

use PE\Component\WAMP\Message\GoodbyeMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\WelcomeMessage;
use PE\Component\WAMP\Router\Router;
use PE\Component\WAMP\Router\Session\SessionInterface;
use PE\Component\WAMP\Router\Session\SessionModule;
use PE\Component\WAMP\Util\EventsInterface;
use PHPUnit\Framework\TestCase;

class SessionModuleTest extends TestCase
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

    public function testOnMessageReceivedHELLO()
    {
        $module = new SessionModule();

        $session = $this->createMock(SessionInterface::class);
        $session->expects(self::once())->method('setSessionID');
        $session->expects(self::once())->method('send')->with(self::isInstanceOf(WelcomeMessage::class));

        $module->onMessageReceived(new HelloMessage('realm', []), $session);
    }

    public function testOnMessageReceivedGOODBYE()
    {
        $module = new SessionModule();

        $session = $this->createMock(SessionInterface::class);
        $session->expects(self::once())->method('send')->with(self::isInstanceOf(GoodbyeMessage::class));
        $session->expects(self::once())->method('shutdown');

        $module->onMessageReceived(new GoodbyeMessage([], 'foo'), $session);
    }
}
