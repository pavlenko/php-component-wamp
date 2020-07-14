<?php

namespace PE\Component\WAMP\Client\Role\Publisher\Feature;

final class BlackWhiteListByAuthRole extends BlackWhiteListBase
{
    public function getBlackListKey()
    {
        return 'exclude_authrole';
    }

    public function getWhiteListKey()
    {
        return 'eligible_authrole';
    }
}
