<?php

namespace PE\Component\WAMP\Tests\Client;

use PE\Component\WAMP\Client\Call;
use PHPUnit\Framework\TestCase;
use React\Promise\Deferred;

final class CallTest extends TestCase
{
    public function testConstructor(): void
    {
        $requestID = 123456;
        $deferred  = $this->createMock(Deferred::class);

        $call = new Call($requestID, $deferred);

        $this->assertSame($call->getRequestID(), $requestID);
        $this->assertSame($call->getDeferred(), $deferred);
    }
}