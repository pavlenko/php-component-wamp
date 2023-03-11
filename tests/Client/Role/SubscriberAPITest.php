<?php

namespace PE\Component\WAMP\Tests\Client\Role;

use PE\Component\WAMP\Client\Role\SubscriberAPI;
use PE\Component\WAMP\Client\Session\SessionInterface;
use PE\Component\WAMP\Message\SubscribeMessage;
use PE\Component\WAMP\Message\UnsubscribeMessage;
use PE\Component\WAMP\SessionBaseTrait;
use PE\Component\WAMP\Tests\Client\Session\SessionStub;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use React\Promise\PromiseInterface;
use React\Promise\RejectedPromise;

final class SubscriberAPITest extends TestCase
{
    /**
     * @return SessionInterface|MockObject
     */
    private function createSessionMock()
    {
        return $this->getMockForAbstractClass(SessionStub::class, [], '', false, true, true, ['send']);
    }

    public function testSubscribe()
    {
        $session = $this->createSessionMock();
        $session->expects(self::once())->method('send')->with(self::isInstanceOf(SubscribeMessage::class));

        $res = (new SubscriberAPI($session))->subscribe('topic', fn() => null);

        self::assertInstanceOf(PromiseInterface::class, $res);
    }

    public function testUnsubscribeFail()
    {
        $session = $this->createSessionMock();

        $res = (new SubscriberAPI($session))->unsubscribe('topic', fn() => null);

        self::assertInstanceOf(RejectedPromise::class, $res);
    }

    public function testUnsubscribePass()
    {
        $closure = \Closure::fromCallable(fn() => null);
        $session = $this->createSessionMock();
        $session->expects(self::exactly(2))->method('send');//->with(self::isInstanceOf(UnsubscribeMessage::class));

        $api = new SubscriberAPI($session);
        $api->subscribe('topic', $closure);

        self::assertInstanceOf(PromiseInterface::class, $api->unsubscribe('topic', $closure));
    }
}