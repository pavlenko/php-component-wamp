<?php

namespace PE\Component\WAMP\Tests\Client\Role;

use PE\Component\WAMP\Client\ClientInterface;
use PE\Component\WAMP\Client\DTO\Registration;
use PE\Component\WAMP\Client\Role\CalleeFeatureInterface;
use PE\Component\WAMP\Client\Role\CalleeModule;
use PE\Component\WAMP\Message\ErrorMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\InterruptMessage;
use PE\Component\WAMP\Message\InvocationMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\RegisteredMessage;
use PE\Component\WAMP\Message\UnregisteredMessage;
use PE\Component\WAMP\Message\YieldMessage;
use PE\Component\WAMP\Tests\Client\TestCaseBase;
use PE\Component\WAMP\Util\EventsInterface;
use React\Promise\Deferred;
use React\Promise\Promise;
use function PHPUnit\Framework\isInstanceOf;

final class CalleeModuleTest extends TestCaseBase
{
    public function testAttach()
    {
        $module = new CalleeModule();

        $events = $this->createMock(EventsInterface::class);
        $events->expects(self::exactly(2))->method('attach')->withConsecutive(
            [ClientInterface::EVENT_MESSAGE_RECEIVED, [$module, 'onMessageReceived']],
            [ClientInterface::EVENT_MESSAGE_SEND, [$module, 'onMessageSend']],
        );

        $module->attach($events);
    }

    public function testDetach()
    {
        $module = new CalleeModule();

        $events = $this->createMock(EventsInterface::class);
        $events->expects(self::exactly(2))->method('detach')->withConsecutive(
            [ClientInterface::EVENT_MESSAGE_RECEIVED, [$module, 'onMessageReceived']],
            [ClientInterface::EVENT_MESSAGE_SEND, [$module, 'onMessageSend']],
        );

        $module->detach($events);
    }

    public function testOnMessageReceivedREGISTERED()
    {
        $message = new RegisteredMessage(1, 1);
        $session = $this->createSessionMock();

        $executed = false;
        $deferred = new Deferred();
        $deferred->promise()->then(function () use (&$executed) {
            $executed = true;
        });

        $registration = new Registration('uri', fn() => null, 1, $deferred);

        $session->registrations = [$registration];

        $module = new CalleeModule();
        $module->onMessageReceived($message, $session);

        self::assertTrue($executed);
    }

    public function testOnMessageReceivedUNREGISTERED()
    {
        $message = new UnregisteredMessage(1);
        $session = $this->createSessionMock();

        $executed = false;
        $deferred = new Deferred();
        $deferred->promise()->then(function () use (&$executed) {
            $executed = true;
        });

        $registration = new Registration('uri', fn() => null, 1, new Deferred());
        $registration->setUnregisterRequestID(1);
        $registration->setUnregisterDeferred($deferred);

        $session->registrations = [$registration];

        $module = new CalleeModule();
        $module->onMessageReceived($message, $session);

        self::assertTrue($executed);
        self::assertCount(0, $session->registrations);
    }

    public function testOnMessageReceivedINVOCATION_exception()
    {
        $message = new InvocationMessage(1, 1, []);
        $session = $this->createSessionMock();
        $session->expects(self::once())->method('send')->with(self::isInstanceOf(ErrorMessage::class));

        $callback = function () {
            throw new \Exception();
        };

        $registration = new Registration('uri', $callback, 1, new Deferred());
        $registration->setRegistrationID(1);

        $session->registrations = [$registration];

        $module = new CalleeModule();
        $module->onMessageReceived($message, $session);
    }

    public function testOnMessageReceivedINVOCATION_void()
    {
        $message = new InvocationMessage(1, 1, []);
        $session = $this->createSessionMock();

        $executed = false;
        $callback = function () use (&$executed) {
            $executed = true;
        };

        $registration = new Registration('uri', $callback, 1, new Deferred());
        $registration->setRegistrationID(1);

        $session->registrations = [$registration];

        $module = new CalleeModule();
        $module->onMessageReceived($message, $session);

        self::assertTrue($executed);
    }

    public function testOnMessageReceivedINVOCATION_resolve()
    {
        $message = new InvocationMessage(1, 1, []);
        $session = $this->createSessionMock();
        $session->expects(self::once())->method('send')->willReturnCallback(function (YieldMessage $message) {
            self::assertSame(['resolve'], $message->getArguments());
        });

        $promise = new Promise(fn(callable $resolve) => $resolve('resolve'));

        $registration = new Registration('uri', fn() => $promise, 1, new Deferred());
        $registration->setRegistrationID(1);

        $session->registrations = [$registration];

        $module = new CalleeModule();
        $module->onMessageReceived($message, $session);
    }

    public function testOnMessageReceivedINVOCATION_reject()
    {
        $message = new InvocationMessage(1, 1, []);
        $session = $this->createSessionMock();
        $session->expects(self::once())->method('send')->willReturnCallback(function (ErrorMessage $message) {
            self::assertSame([isInstanceOf(\Exception::class)], $message->getArguments());
        });

        $promise = new Promise(fn(callable $_1, callable $reject) => $reject(new \Exception('reject')));

        $registration = new Registration('uri', fn() => $promise, 1, new Deferred());
        $registration->setRegistrationID(1);

        $session->registrations = [$registration];

        $module = new CalleeModule();
        $module->onMessageReceived($message, $session);
    }

    public function testOnMessageReceivedINTERRUPT()
    {
        $message = new InterruptMessage(1, []);
        $session = $this->createSessionMock();

        $executed  = false;
        $canceller = function () use (&$executed) {
            $executed = true;
        };

        $session->invocationCancellers = [1 => $canceller];
        $session->expects(self::once())->method('send')->with(self::isInstanceOf(ErrorMessage::class));

        $module = new CalleeModule();
        $module->onMessageReceived($message, $session);

        self::assertTrue($executed);
    }

    public function testOnMessageReceivedERROR_register()
    {
        $message = new ErrorMessage(Message::CODE_REGISTER, 1, [], 'uri');
        $session = $this->createSessionMock();

        $executed = false;
        $deferred = new Deferred();
        $deferred->promise()->otherwise(function () use (&$executed) {
            $executed = true;
        });

        $registration = new Registration('uri', fn() => null, 1, $deferred);

        $session->registrations = [$registration];

        $module = new CalleeModule();
        $module->onMessageReceived($message, $session);

        self::assertTrue($executed);
        self::assertCount(0, $session->registrations);
    }

    public function testOnMessageReceivedERROR_unregister()
    {
        $message = new ErrorMessage(Message::CODE_UNREGISTER, 1, [], 'uri');
        $session = $this->createSessionMock();

        $executed = false;
        $deferred = new Deferred();
        $deferred->promise()->otherwise(function () use (&$executed) {
            $executed = true;
        });

        $registration = new Registration('uri', fn() => null, 1, new Deferred());
        $registration->setUnregisterRequestID(1);
        $registration->setUnregisterDeferred($deferred);

        $session->registrations = [$registration];

        $module = new CalleeModule();
        $module->onMessageReceived($message, $session);

        self::assertTrue($executed);
        self::assertCount(0, $session->registrations);
    }

    public function testOnMessageSend()
    {
        $message = new HelloMessage('realm', []);
        $feature = $this->createMock(CalleeFeatureInterface::class);
        $feature->expects(self::once())->method('getName')->willReturn('feature_name');

        $module = new CalleeModule($feature);
        $module->onMessageSend($message);

        self::assertNotEmpty($message->getFeatures('callee'));
    }
}