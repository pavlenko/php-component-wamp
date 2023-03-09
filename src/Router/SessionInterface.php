<?php

namespace PE\Component\WAMP\Router;

use PE\Component\WAMP\SessionBaseInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface as SymfonySessionInterface;

/**
 * @property SymfonySessionInterface|null $session
 * @property string|null $challenge
 * @property string|null $token
 * @property string|null $authMethod
 */
interface SessionInterface extends SessionBaseInterface
{
    // Nothing to implement, just typed interface
}