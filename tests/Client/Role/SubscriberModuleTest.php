<?php

namespace PE\Component\WAMP\Tests\Client\Role;

use PE\Component\WAMP\Client\Client;
use PE\Component\WAMP\Client\Role\SubscriberFeatureInterface;
use PE\Component\WAMP\Client\Role\SubscriberModule;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Util\EventsInterface;
use PHPUnit\Framework\TestCase;

final class SubscriberModuleTest extends TestCase
{
    public function testAttach()
    {
        $module = new SubscriberModule();

        $events = $this->createMock(EventsInterface::class);
        $events->expects(self::exactly(2))->method('attach')->withConsecutive(
            [Client::EVENT_MESSAGE_RECEIVED, [$module, 'onMessageReceived']],
            [Client::EVENT_MESSAGE_SEND, [$module, 'onMessageSend']],
        );

        $module->attach($events);
    }

    public function testDetach()
    {
        $module = new SubscriberModule();

        $events = $this->createMock(EventsInterface::class);
        $events->expects(self::exactly(2))->method('detach')->withConsecutive(
            [Client::EVENT_MESSAGE_RECEIVED, [$module, 'onMessageReceived']],
            [Client::EVENT_MESSAGE_SEND, [$module, 'onMessageSend']],
        );

        $module->detach($events);
    }

    //TODO on receive tests

    public function testOnMessageSendHELLO()
    {
        $message = new HelloMessage('realm', []);
        $feature = $this->createMock(SubscriberFeatureInterface::class);
        $feature->expects(self::once())->method('getName')->willReturn('feature_name');

        $module = new SubscriberModule($feature);
        $module->onMessageSend($message);

        self::assertNotEmpty($message->getFeatures('subscriber'));
    }
}