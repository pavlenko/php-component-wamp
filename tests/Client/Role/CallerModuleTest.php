<?php

namespace PE\Component\WAMP\Tests\Client\Role;

use PE\Component\WAMP\Client\ClientInterface;
use PE\Component\WAMP\Client\DTO\Call;
use PE\Component\WAMP\Client\Role\CallerFeatureInterface;
use PE\Component\WAMP\Client\Role\CallerModule;
use PE\Component\WAMP\Message\ErrorMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\ResultMessage;
use PE\Component\WAMP\Tests\Client\TestCaseBase;
use PE\Component\WAMP\Util\EventsInterface;
use React\Promise\Deferred;

class CallerModuleTest extends TestCaseBase
{
    public function testAttach()
    {
        $module = new CallerModule();

        $events = $this->createMock(EventsInterface::class);
        $events->expects(self::exactly(2))->method('attach')->withConsecutive(
            [ClientInterface::EVENT_MESSAGE_RECEIVED, [$module, 'onMessageReceived']],
            [ClientInterface::EVENT_MESSAGE_SEND, [$module, 'onMessageSend']],
        );

        $module->attach($events);
    }

    public function testDetach()
    {
        $module = new CallerModule();

        $events = $this->createMock(EventsInterface::class);
        $events->expects(self::exactly(2))->method('detach')->withConsecutive(
            [ClientInterface::EVENT_MESSAGE_RECEIVED, [$module, 'onMessageReceived']],
            [ClientInterface::EVENT_MESSAGE_SEND, [$module, 'onMessageSend']],
        );

        $module->detach($events);
    }

    public function testOnMessageReceivedRESULT_default()
    {
        $message = new ResultMessage(1, []);
        $session = $this->createSessionMock();

        $executed = false;
        $deferred = new Deferred();
        $deferred->promise()->then(function () use (&$executed) {
            $executed = true;
        });

        $session->callRequests = [new Call(1, $deferred)];

        $module = new CallerModule();
        $module->onMessageReceived($message, $session);

        self::assertTrue($executed);
    }

    public function testOnMessageReceivedRESULT_progress()
    {
        $message = new ResultMessage(1, ['progress' => true]);
        $session = $this->createSessionMock();

        $executed = false;
        $deferred = new Deferred();
        $deferred->promise()->progress(function () use (&$executed) {
            $executed = true;
        });

        $session->callRequests = [new Call(1, $deferred)];

        $module = new CallerModule();
        $module->onMessageReceived($message, $session);

        self::assertTrue($executed);
    }

    public function testOnMessageReceivedERROR()
    {
        $message = new ErrorMessage(Message::CODE_CALL, 1, [], 'uri');
        $session = $this->createSessionMock();

        $executed = false;
        $deferred = new Deferred();
        $deferred->promise()->otherwise(function () use (&$executed) {
            $executed = true;
        });

        $session->callRequests = [new Call(1, $deferred)];

        $module = new CallerModule();
        $module->onMessageReceived($message, $session);

        self::assertTrue($executed);
    }

    public function testOnMessageSend()
    {
        $message = new HelloMessage('realm', []);
        $feature = $this->createMock(CallerFeatureInterface::class);
        $feature->expects(self::once())->method('getName')->willReturn('feature_name');

        $module = new CallerModule($feature);
        $module->onMessageSend($message);

        self::assertNotEmpty($message->getFeatures('caller'));
    }
}