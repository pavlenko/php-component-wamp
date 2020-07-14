<?php

namespace PE\Component\WAMP\Client\Role\Publisher\Feature;

interface BlackWhiteListInterface
{
    /**
     * @return string
     */
    public function getBlackListKey();

    /**
     * @param string $topic
     *
     * @return array
     */
    public function getBlackListItems($topic);

    /**
     * @return string
     */
    public function getWhiteListKey();

    /**
     * @param string $topic
     *
     * @return array
     */
    public function getWhiteListItems($topic);
}
