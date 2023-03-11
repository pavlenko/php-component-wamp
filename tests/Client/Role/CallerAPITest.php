<?php

namespace PE\Component\WAMP\Tests\Client\Role;

use PE\Component\WAMP\Client\Role\CallerAPI;
use PE\Component\WAMP\Client\Session\SessionInterface;
use PE\Component\WAMP\Message\CallMessage;
use PE\Component\WAMP\Message\CancelMessage;
use PE\Component\WAMP\Tests\Client\Session\SessionStub;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use React\Promise\CancellablePromiseInterface;

final class CallerAPITest extends TestCase
{
    /**
     * @return SessionInterface|MockObject
     */
    private function createSessionMock()
    {
        return $this->getMockForAbstractClass(SessionStub::class, [], '', false, true, true, ['send']);
    }

    public function testCall()
    {
        $session = $this->createSessionMock();
        $session->expects(self::once())->method('send')->with(self::isInstanceOf(CallMessage::class));

        $res = (new CallerAPI($session))->call('procedure');

        self::assertInstanceOf(CancellablePromiseInterface::class, $res);
    }
}