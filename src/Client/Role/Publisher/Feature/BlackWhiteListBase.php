<?php

namespace PE\Component\WAMP\Client\Role\Publisher\Feature;

abstract class BlackWhiteListBase implements BlackWhiteListInterface
{
    private $blackList;
    private $whiteList;

    /**
     * @param array $blackList
     * @param array $whiteList
     */
    public function __construct(array $blackList, array $whiteList)
    {
        $this->blackList = $blackList;
        $this->whiteList = $whiteList;
    }

    public function getBlackListItems($topic)
    {
        return $this->blackList;
    }

    public function getWhiteListItems($topic)
    {
        return $this->whiteList;
    }
}
