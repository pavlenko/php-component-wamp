<?php

namespace PE\Component\WAMP\Tests\Client;

use PE\Component\WAMP\Client\Call;
use PE\Component\WAMP\Client\CallCollection;
use PHPUnit\Framework\TestCase;

final class CallCollectionTest extends TestCase
{
    public function testChange(): void
    {
        $call = $this->createMock(Call::class);
        $call->method('getRequestID')->willReturn(123);

        $collection = new CallCollection();
        $collection->add($call);

        $this->assertSame($call, $collection->findByRequestID(123));

        $collection->remove($call);

        $this->assertNull($collection->findByRequestID(123));
    }
}