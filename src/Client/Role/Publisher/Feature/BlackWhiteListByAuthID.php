<?php

namespace PE\Component\WAMP\Client\Role\Publisher\Feature;

final class BlackWhiteListByAuthID extends BlackWhiteListBase
{
    public function getBlackListKey()
    {
        return 'exclude_authid';
    }

    public function getWhiteListKey()
    {
        return 'eligible_authid';
    }
}
