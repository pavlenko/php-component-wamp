<?php

namespace PE\Component\WAMP\Client;

class CallCollection
{
    /**
     * @var Call[]
     */
    private array $calls = [];

    /**
     * @param Call $call
     */
    public function add(Call $call): void
    {
        $this->calls[spl_object_hash($call)] = $call;
    }

    /**
     * @param Call $call
     */
    public function remove(Call $call): void
    {
        if ($key = array_search($call, $this->calls, true)) {
            unset($this->calls[$key]);
        }
    }

    /**
     * @param int $id
     *
     * @return Call|null
     */
    public function findByRequestID(int $id): ?Call
    {
        $filtered = array_filter($this->calls, function (Call $call) use ($id) {
            return $call->getRequestID() === $id;
        });

        return current($filtered) ?: null;
    }
}