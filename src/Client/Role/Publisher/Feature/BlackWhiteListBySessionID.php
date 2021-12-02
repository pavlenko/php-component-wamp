<?php

namespace PE\Component\WAMP\Client\Role\Publisher\Feature;

final class BlackWhiteListBySessionID extends BlackWhiteListBase
{
    public function getBlackListKey(): string
    {
        return 'exclude';
    }

    public function getWhiteListKey(): string
    {
        return 'eligible';
    }
}
