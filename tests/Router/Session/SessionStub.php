<?php

namespace PE\Component\WAMP\Tests\Router\Session;

use PE\Component\WAMP\Router\Session\SessionInterface;
use PE\Component\WAMP\SessionBaseTrait;

abstract class SessionStub implements SessionInterface
{
    use SessionBaseTrait;
}