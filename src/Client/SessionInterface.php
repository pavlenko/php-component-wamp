<?php

namespace PE\Component\WAMP\Client;

use PE\Component\WAMP\SessionBaseInterface;
use React\Promise\Deferred;

/**
 * @property array<int, Deferred> $publishRequests
 */
interface SessionInterface extends SessionBaseInterface
{
    // Nothing to implement, just typed interface
}