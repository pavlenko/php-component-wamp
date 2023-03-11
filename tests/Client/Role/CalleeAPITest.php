<?php

namespace PE\Component\WAMP\Tests\Client\Role;

use PE\Component\WAMP\Client\Registration;
use PE\Component\WAMP\Client\Role\CalleeAPI;
use PE\Component\WAMP\Client\Session\SessionInterface;
use PE\Component\WAMP\Message\RegisterMessage;
use PE\Component\WAMP\Message\UnregisterMessage;
use PE\Component\WAMP\Tests\Client\Session\SessionStub;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use React\Promise\RejectedPromise;

final class CalleeAPITest extends TestCase
{
    /**
     * @return SessionInterface|MockObject
     */
    private function createSessionMock()
    {
        return $this->getMockForAbstractClass(SessionStub::class, [], '', false, true, true, ['send']);
    }

    public function testRegister_fail()
    {
        $this->expectException(\InvalidArgumentException::class);

        $session = $this->createSessionMock();
        $session->registrations = [new Registration('uri', fn() => null, 0, new Deferred())];

        $api = new CalleeAPI($session);
        $api->register('uri', fn() => null);
    }

    public function testRegister_pass()
    {
        $session = $this->createSessionMock();
        $session->expects(self::once())->method('send')->with(self::isInstanceOf(RegisterMessage::class));

        $res = (new CalleeAPI($session))->register('uri', fn() => null);

        self::assertInstanceOf(PromiseInterface::class, $res);
    }

    public function testUnregister_fail()
    {
        $session = $this->createSessionMock();

        $res = (new CalleeAPI($session))->unregister('uri');

        self::assertInstanceOf(RejectedPromise::class, $res);
    }

    public function testUnregister_pass()
    {
        $session = $this->createSessionMock();
        $session->expects(self::once())->method('send')->with(self::isInstanceOf(UnregisterMessage::class));

        $registration = new Registration('uri', fn() => null, 0, new Deferred());

        $session->registrations = [$registration];

        $res = (new CalleeAPI($session))->unregister('uri');

        self::assertInstanceOf(PromiseInterface::class, $res);
    }
}