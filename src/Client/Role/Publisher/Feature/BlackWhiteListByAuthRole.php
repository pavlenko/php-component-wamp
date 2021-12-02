<?php

namespace PE\Component\WAMP\Client\Role\Publisher\Feature;

final class BlackWhiteListByAuthRole extends BlackWhiteListBase
{
    public function getBlackListKey(): string
    {
        return 'exclude_authrole';
    }

    public function getWhiteListKey(): string
    {
        return 'eligible_authrole';
    }
}
