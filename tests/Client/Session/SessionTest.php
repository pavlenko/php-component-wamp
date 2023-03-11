<?php

namespace PE\Component\WAMP\Tests\Client\Session;

use PE\Component\WAMP\Client\ClientInterface;
use PE\Component\WAMP\Client\Session\Session;
use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\Message\Message;
use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    public function testSend()
    {
        $conn    = $this->createMock(ConnectionInterface::class);
        $client  = $this->createMock(ClientInterface::class);
        $message = $this->getMockForAbstractClass(Message::class);
        $session = new Session($conn, $client);

        $conn->expects(self::once())->method('send')->with($message);
        $client->expects(self::once())->method('processMessageSend')->with($message);

        $session->send($message);
    }
}