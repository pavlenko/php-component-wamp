<?php

namespace PE\Component\WAMP\Tests\Router\Role;

use PE\Component\WAMP\Message\ErrorMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\SubscribedMessage;
use PE\Component\WAMP\Message\SubscribeMessage;
use PE\Component\WAMP\Message\UnsubscribedMessage;
use PE\Component\WAMP\Message\UnsubscribeMessage;
use PE\Component\WAMP\Message\WelcomeMessage;
use PE\Component\WAMP\Router\Role\BrokerFeatureInterface;
use PE\Component\WAMP\Router\Role\BrokerModule;
use PE\Component\WAMP\Router\Router;
use PE\Component\WAMP\Router\Session\SessionInterface;
use PE\Component\WAMP\Util\EventsInterface;
use PHPUnit\Framework\TestCase;

final class BrokerModuleTest extends TestCase
{
    public function testAttach()
    {
        $module = new BrokerModule();

        $events = $this->createMock(EventsInterface::class);
        $events->expects(self::exactly(3))->method('attach')->withConsecutive(
            [Router::EVENT_MESSAGE_RECEIVED, [$module, 'onMessageReceived']],
            [Router::EVENT_MESSAGE_SEND, [$module, 'onMessageSend']],
            [Router::EVENT_CONNECTION_CLOSE, [$module, 'onConnectionClose']],
        );

        $module->attach($events);
    }

    public function testDetach()
    {
        $module = new BrokerModule();

        $events = $this->createMock(EventsInterface::class);
        $events->expects(self::exactly(3))->method('detach')->withConsecutive(
            [Router::EVENT_MESSAGE_RECEIVED, [$module, 'onMessageReceived']],
            [Router::EVENT_MESSAGE_SEND, [$module, 'onMessageSend']],
            [Router::EVENT_CONNECTION_CLOSE, [$module, 'onConnectionClose']],
        );

        $module->detach($events);
    }

    public function testOnMessageReceivedPUBLISH()
    {
        $this->markTestIncomplete();
    }

    public function testOnMessageReceivedSUBSCRIBE_pass()
    {
        $message = new SubscribeMessage(1, [], 'topic');
        $session = $this->createMock(SessionInterface::class);
        $session->expects(self::once())->method('send')->with(self::isInstanceOf(SubscribedMessage::class));

        $module = new BrokerModule();
        $module->onMessageReceived($message, $session);
    }

    public function testOnMessageReceivedSUBSCRIBE_fail()
    {
        $message = new SubscribeMessage(1, [], '');
        $session = $this->createMock(SessionInterface::class);
        $session->expects(self::once())->method('send')->with(self::isInstanceOf(ErrorMessage::class));

        $module = new BrokerModule();
        $module->onMessageReceived($message, $session);
    }

    public function testOnMessageReceivedUNSUBSCRIBE_pass()
    {
        $subscriptionID = 0;

        $index   = 0;
        $session = $this->createMock(SessionInterface::class);
        $session->expects(self::exactly(2))->method('send')->willReturnCallback(
            function (Message $message) use (&$index, &$subscriptionID) {
                if (0 === $index) {
                    self::assertInstanceOf(SubscribedMessage::class, $message);
                    /* @var $message SubscribedMessage */
                    $subscriptionID = $message->getSubscriptionID();
                } else {
                    self::assertInstanceOf(UnsubscribedMessage::class, $message);
                }
                $index++;
            }
        );

        $module = new BrokerModule();
        $module->onMessageReceived(new SubscribeMessage(1, [], 'topic'), $session);
        $module->onMessageReceived(new UnsubscribeMessage(1, $subscriptionID), $session);
    }

    public function testOnMessageReceivedUNSUBSCRIBE_fail()
    {
        $this->markTestIncomplete();
    }

    public function testOnMessageSendWELCOME()
    {
        $message = new WelcomeMessage(1, []);
        $feature = $this->createMock(BrokerFeatureInterface::class);
        $feature->expects(self::once())->method('getName')->willReturn('feature_name');

        $module = new BrokerModule($feature);
        $module->onMessageSend($message);

        self::assertNotEmpty($message->getFeatures('broker'));
    }
}