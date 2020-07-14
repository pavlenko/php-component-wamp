<?php

namespace PE\Component\WAMP\Client\Role\Publisher\Feature;

final class BlackWhiteListBySessionID extends BlackWhiteListBase
{
    public function getBlackListKey()
    {
        return 'exclude';
    }

    public function getWhiteListKey()
    {
        return 'eligible';
    }
}
