<?php

namespace PE\Component\WAMP\Tests\Client\Role;

use PE\Component\WAMP\Client\Registration;
use PE\Component\WAMP\Client\Role\CalleeAPI;
use PE\Component\WAMP\Client\Session\SessionInterface;
use PE\Component\WAMP\Message\RegisterMessage;
use PE\Component\WAMP\Tests\Client\Session\SessionStub;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use React\Promise\PromiseInterface;

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
        $session->registrations = [new Registration('uri', fn() => null)];

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
}