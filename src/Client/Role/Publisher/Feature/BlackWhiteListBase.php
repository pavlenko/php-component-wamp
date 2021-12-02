<?php

namespace PE\Component\WAMP\Client\Role\Publisher\Feature;

abstract class BlackWhiteListBase implements BlackWhiteListInterface
{
    private array $blackList;
    private array $whiteList;

    public function __construct(array $blackList, array $whiteList)
    {
        $this->blackList = $blackList;
        $this->whiteList = $whiteList;
    }

    public function getBlackListItems(string $topic): array
    {
        //TODO
        return $this->blackList;
    }

    public function getWhiteListItems(string $topic): array
    {
        //TODO
        return $this->whiteList;
    }
}
