<?php

namespace PE\Component\WAMP;

trait Events
{
    /**
     * @var array
     */
    private $listeners = [];

    /**
     * @param string   $event
     * @param callable $listener
     * @param int      $priority
     */
    public function on(string $event, callable $listener, int $priority = 0)
    {
        $this->listeners[$event][$priority][] = $listener;
    }

    /**
     * @param string   $event
     * @param callable $listener
     */
    public function off(string $event, callable $listener)
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

    /**
     * @param string $event
     * @param mixed  ...$arguments
     */
    public function emit(string $event, ...$arguments)
    {
        if (empty($this->listeners[$event])) {
            return;
        }

        ksort($this->listeners[$event]);

        foreach ($this->listeners[$event] as $listeners) {
            foreach ($listeners as $listener) {
                if (false === $listener(...$arguments)) {
                    // For stop event propagation listener must return FALSE
                    return;
                }
            }
        }
    }
}