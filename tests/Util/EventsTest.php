<?php

namespace PE\Component\WAMP\Tests\Util;

use PE\Component\WAMP\Util\Events;
use PHPUnit\Framework\TestCase;

final class EventsTest extends TestCase
{
    public function testTrigger(): void
    {
        $events = new Events();
        $count  = 0;

        $handler = function () use (&$count) {
            $count++;
            return false;//<-- this will break execution of other listeners
        };

        $events->trigger('event');

        self::assertSame(0, $count);

        $events->attach('event', $handler);
        $events->attach('event', $handler2 = fn() => 1);
        $triggered = $events->trigger('event');

        self::assertSame(1, $count);
        self::assertSame(1, $triggered);

        $events->detach('event', $handler);
        $events->detach('event', $handler2);
        $events->detach('event', $handler2);
        $events->trigger('event');

        self::assertSame(1, $count);
    }
}