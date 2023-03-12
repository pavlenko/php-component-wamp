<?php

namespace PE\Component\WAMP\Tests\Client\Role;

use PE\Component\WAMP\Client\Client;
use PE\Component\WAMP\Client\DTO\Subscription;
use PE\Component\WAMP\Client\Role\SubscriberFeatureInterface;
use PE\Component\WAMP\Client\Role\SubscriberModule;
use PE\Component\WAMP\Client\Session\SessionInterface;
use PE\Component\WAMP\Message\ErrorMessage;
use PE\Component\WAMP\Message\EventMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\SubscribedMessage;
use PE\Component\WAMP\Message\UnsubscribedMessage;
use PE\Component\WAMP\Tests\Client\Session\SessionStub;
use PE\Component\WAMP\Util\EventsInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use React\Promise\Deferred;

final class SubscriberModuleTest extends TestCase
{
    /**
     * @return SessionInterface|MockObject
     */
    private function createSessionMock()
    {
        return $this->getMockForAbstractClass(SessionStub::class, [], '', false, true, true, ['send']);
    }

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

    public function testOnMessageReceivedSUBSCRIBED()
    {
        $message = new SubscribedMessage(1, 1);
        $session = $this->createSessionMock();

        $executed = false;
        $deferred = new Deferred();
        $deferred->promise()->then(function () use (&$executed) {
            $executed = true;
        });

        $subscription = new Subscription('topic', fn() => null);
        $subscription->setSubscribeRequestID(1);
        $subscription->setSubscribeDeferred($deferred);

        $session->subscriptions = [$subscription];

        $module = new SubscriberModule();
        $module->onMessageReceived($message, $session);

        self::assertTrue($executed);
    }

    public function testOnMessageReceivedUNSUBSCRIBED()
    {
        $message = new UnsubscribedMessage(1);
        $session = $this->createSessionMock();

        $executed = false;
        $deferred = new Deferred();
        $deferred->promise()->then(function () use (&$executed) {
            $executed = true;
        });

        $subscription = new Subscription('topic', fn() => null);
        $subscription->setUnsubscribeRequestID(1);
        $subscription->setUnsubscribeDeferred($deferred);

        $session->subscriptions = [$subscription];

        $module = new SubscriberModule();
        $module->onMessageReceived($message, $session);

        self::assertTrue($executed);
    }

    public function testOnMessageReceivedEVENT()
    {
        $message = new EventMessage(1, 1, []);
        $session = $this->createSessionMock();

        $executed     = false;
        $subscription = new Subscription('topic', function () use (&$executed) {
            $executed = true;
        });
        $subscription->setSubscriptionID(1);

        $session->subscriptions = [$subscription];

        $module = new SubscriberModule();
        $module->onMessageReceived($message, $session);

        self::assertTrue($executed);
    }

    public function testOnMessageReceivedERROR_subscribe()
    {
        $message = new ErrorMessage(Message::CODE_SUBSCRIBE, 1, [], 'uri');
        $session = $this->createSessionMock();

        $executed = false;
        $deferred = new Deferred();
        $deferred->promise()->otherwise(function () use (&$executed) {
            $executed = true;
        });

        $subscription = new Subscription('topic', fn() => null);
        $subscription->setSubscribeRequestID(1);
        $subscription->setSubscribeDeferred($deferred);

        $session->subscriptions = [$subscription];

        $module = new SubscriberModule();
        $module->onMessageReceived($message, $session);

        self::assertTrue($executed);
    }

    public function testOnMessageReceivedERROR_unsubscribe()
    {
        $message = new ErrorMessage(Message::CODE_UNSUBSCRIBE, 1, [], 'uri');
        $session = $this->createSessionMock();

        $executed = false;
        $deferred = new Deferred();
        $deferred->promise()->otherwise(function () use (&$executed) {
            $executed = true;
        });

        $subscription = new Subscription('topic', fn() => null);
        $subscription->setUnsubscribeRequestID(1);
        $subscription->setUnsubscribeDeferred($deferred);

        $session->subscriptions = [$subscription];

        $module = new SubscriberModule();
        $module->onMessageReceived($message, $session);

        self::assertTrue($executed);
    }

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