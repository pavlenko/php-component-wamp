<?php

namespace PE\Component\WAMP\Client\Session;

use PE\Component\WAMP\Client\DTO\Call;
use PE\Component\WAMP\Client\DTO\Registration;
use PE\Component\WAMP\Client\DTO\Subscription;
use PE\Component\WAMP\SessionBaseInterface;
use React\Promise\Deferred;

/**
 * @property array<int, Deferred> $publishRequests
 * @property array<int, Registration> $registrations
 * @property array<int, \Closure> $invocationCancellers
 * @property array<int, Call> $callRequests
 * @property array<int, Subscription> $subscriptions
 */
interface SessionInterface extends SessionBaseInterface
{
    // Nothing to implement, just typed interface
}