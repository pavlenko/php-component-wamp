<?php

namespace Publisher\Feature;

final class BlackWhiteListByAuthID extends BlackWhiteListBase
{
    public function getBlackListKey(): string
    {
        return 'exclude_authid';
    }

    public function getWhiteListKey(): string
    {
        return 'eligible_authid';
    }
}
