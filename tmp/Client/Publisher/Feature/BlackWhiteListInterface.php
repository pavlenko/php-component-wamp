<?php

namespace Publisher\Feature;

interface BlackWhiteListInterface
{
    /**
     * @return string
     */
    public function getBlackListKey(): string;

    /**
     * @param string $topic
     *
     * @return array
     */
    public function getBlackListItems(string $topic): array;

    /**
     * @return string
     */
    public function getWhiteListKey(): string;

    /**
     * @param string $topic
     *
     * @return array
     */
    public function getWhiteListItems(string $topic): array;
}
