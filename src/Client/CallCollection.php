<?php

namespace PE\Component\WAMP\Client;

/**
 * @codeCoverageIgnore
 */
final class CallCollection
{
    /**
     * @var Call[]
     */
    private array $calls = [];

    public function add(Call $call): void
    {
        $this->calls[spl_object_hash($call)] = $call;
    }

    public function remove(Call $call): void
    {
        if ($key = array_search($call, $this->calls, true)) {
            unset($this->calls[$key]);
        }
    }

    public function findByRequestID(int $id): ?Call
    {
        $filtered = array_filter($this->calls, function (Call $call) use ($id) {
            return $call->getRequestID() === $id;
        });

        return current($filtered) ?: null;
    }
}