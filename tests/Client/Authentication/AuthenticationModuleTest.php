<?php

namespace PE\Component\WAMP\Tests\Client\Authentication;

use PE\Component\WAMP\Client\Authentication\AuthenticationModule;
use PE\Component\WAMP\Client\Authentication\Method\MethodInterface;
use PE\Component\WAMP\Client\ClientInterface;
use PE\Component\WAMP\Client\Session\SessionInterface;
use PE\Component\WAMP\Message\ChallengeMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Util\EventsInterface;
use PHPUnit\Framework\TestCase;

final class AuthenticationModuleTest extends TestCase
{
    public function testAttach()
    {
        $module = new AuthenticationModule();

        $events = $this->createMock(EventsInterface::class);
        $events->expects(self::exactly(2))->method('attach')->withConsecutive(
            [ClientInterface::EVENT_MESSAGE_RECEIVED, [$module, 'onMessageReceived'], -10],
            [ClientInterface::EVENT_MESSAGE_SEND, [$module, 'onMessageSend']],
        );

        $module->attach($events);
    }

    public function testDetach()
    {
        $module = new AuthenticationModule();

        $events = $this->createMock(EventsInterface::class);
        $events->expects(self::exactly(2))->method('detach')->withConsecutive(
            [ClientInterface::EVENT_MESSAGE_RECEIVED, [$module, 'onMessageReceived']],
            [ClientInterface::EVENT_MESSAGE_SEND, [$module, 'onMessageSend']],
        );

        $module->detach($events);
    }

    public function testOnMessageReceivedException()
    {
        $this->expectException(\LogicException::class);

        $session = $this->createMock(SessionInterface::class);

        $module = new AuthenticationModule();
        $module->onMessageReceived(new ChallengeMessage('foo', []), $session);
    }

    public function testOnMessageReceivedCHALLENGE()
    {
        $message = new ChallengeMessage('method', []);
        $session = $this->createMock(SessionInterface::class);

        $method = $this->createMock(MethodInterface::class);
        $method->expects(self::once())->method('getName')->willReturn('method');
        $method->expects(self::once())->method('processChallengeMessage')->with($session, $message);

        $module = new AuthenticationModule($method);
        $module->onMessageReceived($message, $session);
    }

    public function testOnMessageSendHELLO()
    {
        $message = new HelloMessage('realm', []);
        $session = $this->createMock(SessionInterface::class);

        $method = $this->createMock(MethodInterface::class);
        $method->expects(self::once())->method('getName')->willReturn('method');
        $method->expects(self::once())->method('processHelloMessage')->with($session, $message);


        $module = new AuthenticationModule($method);
        $module->onMessageSend($message, $session);

        self::assertSame(['method'], $message->getDetail('authmethods'));
    }
}