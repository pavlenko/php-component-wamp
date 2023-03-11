<?php

namespace PE\Component\WAMP\Tests\Client\Session;

use PE\Component\WAMP\Client\Session\SessionInterface;
use PE\Component\WAMP\SessionBaseTrait;

abstract class SessionStub implements SessionInterface
{
    use SessionBaseTrait;
}