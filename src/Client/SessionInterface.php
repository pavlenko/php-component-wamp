<?php

namespace PE\Component\WAMP\Client;

use PE\Component\WAMP\SessionBaseInterface;
use React\Promise\Deferred;

/**
 * @property array<int, Deferred> $publishRequests
 * @property RegistrationCollection $registrations
 * @property array<int, callable> $invocationCancellers
 * @property array<int, Call> $callRequests
 * @property SubscriptionCollection $subscriptions
 */
interface SessionInterface extends SessionBaseInterface
{
    // Nothing to implement, just typed interface
}