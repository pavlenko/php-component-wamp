<?php

namespace PE\Component\WAMP\Util;

final class Events implements EventsInterface
{
    /**
     * @var array<string, array<int, callable>>
     */
    private array $listeners = [];

    public function attach(string $event, callable $listener, int $priority = 0): void
    {
        $this->listeners[$event][$priority][] = $listener;
    }

    public function detach(string $event, callable $listener): void
    {
        if (empty($this->listeners[$event])) {
            return;
        }

        foreach ($this->listeners[$event] as $priority => $listeners) {
            foreach ($listeners as $k => $v) {
                if ($v === $listener) {
                    unset($listeners[$k]);
                } else {
                    $listeners[$k] = $v;
                }
            }

            if ($listeners) {
                $this->listeners[$event][$priority] = $listeners;
            } else {
                unset($this->listeners[$event][$priority]);
            }
        }
    }

    public function trigger(string $event, ...$arguments): int
    {
        if (empty($this->listeners[$event])) {
            return 0;
        }

        ksort($this->listeners[$event]);

        $triggered = 0;
        foreach ($this->listeners[$event] as $listeners) {
            foreach ($listeners as $listener) {
                $triggered++;
                if (false === $listener(...$arguments)) {
                    // For stop event propagation listener must return FALSE
                    return $triggered;
                }
            }
        }
        return $triggered;
    }
}