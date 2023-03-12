<?php

namespace PE\Component\WAMP\Tests\Client\Session;

use PE\Component\WAMP\Client\Client;
use PE\Component\WAMP\Client\ClientInterface;
use PE\Component\WAMP\Client\Session\SessionInterface;
use PE\Component\WAMP\Client\Session\SessionModule;
use PE\Component\WAMP\Message\AbortMessage;
use PE\Component\WAMP\Message\GoodbyeMessage;
use PE\Component\WAMP\Message\WelcomeMessage;
use PE\Component\WAMP\Util\EventsInterface;
use PHPUnit\Framework\TestCase;

final class SessionModuleTest extends TestCase
{
    public function testAttach()
    {
        $module = new SessionModule();

        $events = $this->createMock(EventsInterface::class);
        $events->expects(self::once())->method('attach')->with(
            Client::EVENT_MESSAGE_RECEIVED, [$module, 'onMessageReceived']
        );

        $module->attach($events);
    }

    public function testDetach()
    {
        $module = new SessionModule();

        $events = $this->createMock(EventsInterface::class);
        $events->expects(self::once())->method('detach')->with(
            ClientInterface::EVENT_MESSAGE_RECEIVED, [$module, 'onMessageReceived']
        );

        $module->detach($events);
    }

    public function testOnMessageReceivedWELCOME()
    {
        $module = new SessionModule();

        $session = $this->createMock(SessionInterface::class);
        $session->expects(self::once())->method('setSessionID');

        $events = $this->createMock(EventsInterface::class);
        $events->expects(self::once())->method('trigger')->with(
            ClientInterface::EVENT_SESSION_ESTABLISHED,
            $session
        );

        $module->attach($events);
        $module->onMessageReceived(new WelcomeMessage(0, []), $session);
    }

    public function testOnMessageReceivedGOODBYE()
    {
        $module = new SessionModule();

        $session = $this->createMock(SessionInterface::class);
        $session->expects(self::once())->method('send')->with(self::isInstanceOf(GoodbyeMessage::class));
        $session->expects(self::once())->method('shutdown');

        $module->onMessageReceived(new GoodbyeMessage([], 'foo'), $session);
    }

    public function testOnMessageReceivedABORT()
    {
        $module = new SessionModule();

        $session = $this->createMock(SessionInterface::class);
        $session->expects(self::once())->method('shutdown');

        $module->onMessageReceived(new AbortMessage([], 'foo'), $session);
    }
}