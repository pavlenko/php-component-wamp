<?php

namespace PE\Component\WAMP\Tests\Client\Role;

use PE\Component\WAMP\Client\ClientInterface;
use PE\Component\WAMP\Client\Role\PublisherFeatureInterface;
use PE\Component\WAMP\Client\Role\PublisherModule;
use PE\Component\WAMP\Message\ErrorMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\PublishedMessage;
use PE\Component\WAMP\Tests\Client\TestCaseBase;
use PE\Component\WAMP\Util\EventsInterface;
use React\Promise\Deferred;

final class PublisherModuleTest extends TestCaseBase
{
    public function testAttach()
    {
        $module = new PublisherModule();

        $events = $this->createMock(EventsInterface::class);
        $events->expects(self::exactly(2))->method('attach')->withConsecutive(
            [ClientInterface::EVENT_MESSAGE_RECEIVED, [$module, 'onMessageReceived']],
            [ClientInterface::EVENT_MESSAGE_SEND, [$module, 'onMessageSend']],
        );

        $module->attach($events);
    }

    public function testDetach()
    {
        $module = new PublisherModule();

        $events = $this->createMock(EventsInterface::class);
        $events->expects(self::exactly(2))->method('detach')->withConsecutive(
            [ClientInterface::EVENT_MESSAGE_RECEIVED, [$module, 'onMessageReceived']],
            [ClientInterface::EVENT_MESSAGE_SEND, [$module, 'onMessageSend']],
        );

        $module->detach($events);
    }

    public function testOnMessageReceivedPUBLISHED()
    {
        $message = new PublishedMessage(1, 1);
        $session = $this->createSessionMock();

        $executed = false;
        $deferred = new Deferred();
        $deferred->promise()->then(function () use (&$executed) {
            $executed = true;
        });

        $session->publishRequests = [1 => $deferred];

        $module = new PublisherModule();
        $module->onMessageReceived($message, $session);

        self::assertTrue($executed);
    }

    public function testOnMessageReceivedERROR()
    {
        $message = new ErrorMessage(Message::CODE_PUBLISH, 1, [], 'uri');
        $session = $this->createSessionMock();

        $executed = false;
        $deferred = new Deferred();
        $deferred->promise()->otherwise(function () use (&$executed) {
            $executed = true;
        });

        $session->publishRequests = [1 => $deferred];

        $module = new PublisherModule();
        $module->onMessageReceived($message, $session);

        self::assertTrue($executed);
    }

    public function testOnMessageSendHELLO()
    {
        $message = new HelloMessage('realm', []);
        $feature = $this->createMock(PublisherFeatureInterface::class);
        $feature->expects(self::once())->method('getName')->willReturn('feature_name');

        $module = new PublisherModule($feature);
        $module->onMessageSend($message);

        self::assertNotEmpty($message->getFeatures('publisher'));
    }
}