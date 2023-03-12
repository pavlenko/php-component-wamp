<?php

namespace PE\Component\WAMP\Tests\Client;

use PE\Component\WAMP\Client\Session\SessionInterface;
use PE\Component\WAMP\Tests\Client\Session\SessionStub;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class TestCaseBase extends TestCase
{
    /**
     * @return SessionInterface|MockObject
     */
    protected function createSessionMock()
    {
        return $this->getMockForAbstractClass(SessionStub::class, [], '', false, true, true, ['send']);
    }
}