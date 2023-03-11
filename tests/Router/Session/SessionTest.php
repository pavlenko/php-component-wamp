<?php

namespace PE\Component\WAMP\Tests\Router\Session;

use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Router\RouterInterface;
use PE\Component\WAMP\Router\Session\Session;
use PHPUnit\Framework\TestCase;

final class SessionTest extends TestCase
{
    public function testSend()
    {
        $conn    = $this->createMock(ConnectionInterface::class);
        $router  = $this->createMock(RouterInterface::class);
        $message = $this->getMockForAbstractClass(Message::class);
        $session = new Session($conn, $router);

        $conn->expects(self::once())->method('send')->with($message);
        $router->expects(self::once())->method('processMessageSend')->with($conn, $message);

        $session->send($message);
    }
}