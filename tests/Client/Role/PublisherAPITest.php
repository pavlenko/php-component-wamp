<?php

namespace PE\Component\WAMP\Tests\Client\Role;

use PE\Component\WAMP\Client\Role\PublisherAPI;
use PE\Component\WAMP\Client\Session\SessionInterface;
use PE\Component\WAMP\Message\PublishMessage;
use PE\Component\WAMP\Tests\Client\Session\SessionStub;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use React\Promise\PromiseInterface;

final class PublisherAPITest extends TestCase
{
    /**
     * @return SessionInterface|MockObject
     */
    private function createSessionMock()
    {
        return $this->getMockForAbstractClass(SessionStub::class, [], '', false, true, true, ['send']);
    }

    public function testPublish()
    {
        $session = $this->createSessionMock();
        $session->expects(self::once())->method('send')->with(self::isInstanceOf(PublishMessage::class));

        $res = (new PublisherAPI($session))->publish('topic', [], [], ['acknowledge' => true]);

        self::assertInstanceOf(PromiseInterface::class, $res);
    }
}