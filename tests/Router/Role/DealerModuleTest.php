<?php

namespace PE\Component\WAMP\Tests\Router\Role;

use PE\Component\WAMP\Message\CallMessage;
use PE\Component\WAMP\Message\CancelMessage;
use PE\Component\WAMP\Message\ErrorMessage;
use PE\Component\WAMP\Message\InterruptMessage;
use PE\Component\WAMP\Message\InvocationMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\RegisteredMessage;
use PE\Component\WAMP\Message\RegisterMessage;
use PE\Component\WAMP\Message\ResultMessage;
use PE\Component\WAMP\Message\UnregisteredMessage;
use PE\Component\WAMP\Message\UnregisterMessage;
use PE\Component\WAMP\Message\WelcomeMessage;
use PE\Component\WAMP\Message\YieldMessage;
use PE\Component\WAMP\Router\Role\DealerFeatureInterface;
use PE\Component\WAMP\Router\Role\DealerModule;
use PE\Component\WAMP\Router\Router;
use PE\Component\WAMP\Router\Session\SessionInterface;
use PE\Component\WAMP\Util\EventsInterface;
use PHPUnit\Framework\TestCase;

final class DealerModuleTest extends TestCase
{
    public function testAttach()
    {
        $module = new DealerModule();

        $events = $this->createMock(EventsInterface::class);
        $events->expects(self::exactly(2))->method('attach')->withConsecutive(
            [Router::EVENT_MESSAGE_RECEIVED, [$module, 'onMessageReceived']],
            [Router::EVENT_MESSAGE_SEND, [$module, 'onMessageSend']],
        );

        $module->attach($events);
    }

    public function testDetach()
    {
        $module = new DealerModule();

        $events = $this->createMock(EventsInterface::class);
        $events->expects(self::exactly(2))->method('detach')->withConsecutive(
            [Router::EVENT_MESSAGE_RECEIVED, [$module, 'onMessageReceived']],
            [Router::EVENT_MESSAGE_SEND, [$module, 'onMessageSend']],
        );

        $module->detach($events);
    }

    public function testOnMessageReceivedREGISTER_pass()
    {
        $message = new RegisterMessage(1, [], 'uri');
        $session = $this->createMock(SessionInterface::class);
        $session->expects(self::once())->method('send')->with(self::isInstanceOf(RegisteredMessage::class));

        $module = new DealerModule();
        $module->onMessageReceived($message, $session);
    }

    public function testOnMessageReceivedREGISTER_fail()
    {
        $message = new RegisterMessage(1, [], 'uri');
        $session = $this->createMock(SessionInterface::class);
        $session->expects(self::exactly(2))->method('send')->withConsecutive(
            [self::isInstanceOf(RegisteredMessage::class)],
            [self::callback(fn(ErrorMessage $m) => $m->getErrorURI() === Message::ERROR_PROCEDURE_ALREADY_EXISTS)],
        );

        $module = new DealerModule();
        $module->onMessageReceived($message, $session);
        $module->onMessageReceived($message, $session);
    }

    public function testOnMessageReceivedUNREGISTER_pass()
    {
        $registrationID = 0;

        $session = $this->createMock(SessionInterface::class);
        $session->expects(self::exactly(2))->method('send')->withConsecutive(
            [self::callback(function (RegisteredMessage $m) use (&$registrationID) {
                $registrationID = $m->getRegistrationID();
                return true;
            })],
            [self::isInstanceOf(UnregisteredMessage::class)],
        );

        $module = new DealerModule();
        $module->onMessageReceived(new RegisterMessage(1, [], 'uri'), $session);
        $module->onMessageReceived(new UnregisterMessage(1, $registrationID), $session);
    }

    public function testOnMessageReceivedUNREGISTER_fail()
    {
        $session = $this->createMock(SessionInterface::class);
        $session->expects(self::once())->method('send')->willReturnCallback(function(ErrorMessage $m) {
            self::assertSame(Message::ERROR_NO_SUCH_REGISTRATION, $m->getErrorURI());
        });

        $module = new DealerModule();
        $module->onMessageReceived(new UnregisterMessage(1, 0), $session);
    }

    public function testOnMessageReceivedCALL_pass()
    {
        $session = $this->createMock(SessionInterface::class);
        $session->expects(self::exactly(2))->method('send')->withConsecutive(
            [self::isInstanceOf(RegisteredMessage::class)],
            [self::isInstanceOf(InvocationMessage::class)],
        );

        $module = new DealerModule();
        $module->onMessageReceived(new RegisterMessage(1, [], 'uri'), $session);
        $module->onMessageReceived(new CallMessage(1, [], 'uri'), $session);
    }

    public function testOnMessageReceivedCALL_fail()
    {
        $session = $this->createMock(SessionInterface::class);
        $session->expects(self::once())->method('send')->willReturnCallback(function(ErrorMessage $m) {
            self::assertSame(Message::ERROR_NO_SUCH_PROCEDURE, $m->getErrorURI());
        });

        $module = new DealerModule();
        $module->onMessageReceived(new CallMessage(1, [], 'uri'), $session);
    }

    public function testOnMessageReceivedYIELD_pass_progress()
    {
        $session = $this->createMock(SessionInterface::class);
        $session->expects(self::exactly(3))->method('send')->withConsecutive(
            [self::isInstanceOf(RegisteredMessage::class)],
            [self::isInstanceOf(InvocationMessage::class)],
            [self::isInstanceOf(ResultMessage::class)],
        );

        $module = new DealerModule();
        $module->onMessageReceived(new RegisterMessage(1, [], 'uri'), $session);
        $module->onMessageReceived(new CallMessage(1, [], 'uri'), $session);
        $module->onMessageReceived(new YieldMessage(1, ['progress' => true]), $session);
    }

    public function testOnMessageReceivedYIELD_pass_complete()
    {
        $session = $this->createMock(SessionInterface::class);
        $session->expects(self::exactly(3))->method('send')->withConsecutive(
            [self::isInstanceOf(RegisteredMessage::class)],
            [self::isInstanceOf(InvocationMessage::class)],
            [self::isInstanceOf(ResultMessage::class)],
        );

        $module = new DealerModule();
        $module->onMessageReceived(new RegisterMessage(1, [], 'uri'), $session);
        $module->onMessageReceived(new CallMessage(1, [], 'uri'), $session);
        $module->onMessageReceived(new YieldMessage(1, []), $session);
    }

    public function testOnMessageReceivedYIELD_fail()
    {
        $session = $this->createMock(SessionInterface::class);
        $session->expects(self::once())->method('send')->willReturnCallback(function(ErrorMessage $m) {
            self::assertSame(Message::ERROR_NO_SUCH_CALL, $m->getErrorURI());
        });

        $module = new DealerModule();
        $module->onMessageReceived(new YieldMessage(1, []), $session);
    }

    public function testOnMessageReceivedERROR_invocation()
    {
        $this->markTestIncomplete();
    }

    public function testOnMessageReceivedERROR_interrupt_pass()
    {
        $this->markTestIncomplete();
    }

    public function testOnMessageReceivedERROR_interrupt_fail()
    {
        $message = new ErrorMessage(Message::CODE_INTERRUPT, 1, [], 'uri');
        $session = $this->createMock(SessionInterface::class);
        $session->expects(self::once())->method('send')->willReturnCallback(function(ErrorMessage $m) {
            self::assertSame(Message::ERROR_NO_SUCH_CALL, $m->getErrorURI());
        });

        $module = new DealerModule();
        $module->onMessageReceived($message, $session);
    }

    public function testOnMessageSendWELCOME()
    {
        $message = new WelcomeMessage(1, []);
        $feature = $this->createMock(DealerFeatureInterface::class);
        $feature->expects(self::once())->method('getName')->willReturn('feature_name');

        $module = new DealerModule($feature);
        $module->onMessageSend($message);

        self::assertNotEmpty($message->getFeatures('dealer'));
    }
}