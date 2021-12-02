<?php

namespace PE\Component\WAMP\EventDispatcher;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

trait EventDispatcherTrait
{
    private EventDispatcherInterface $dispatcher;

    public function getDispatcher(): EventDispatcherInterface
    {
        return $this->dispatcher;
    }

    public function setDispatcher(EventDispatcherInterface $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @deprecated
     */
    public function on(string $eventName, callable $listener, int $priority = 0): void
    {
        $this->dispatcher->addListener($eventName, $listener, $priority);
    }

    /**
     * @deprecated
     */
    public function off(string $eventName, callable $listener): void
    {
        $this->dispatcher->removeListener($eventName, $listener);
    }

    /**
     * @deprecated
     */
    public function once(string $eventName, callable $listener, int $priority = 0): void
    {
        $onceListener = function () use (&$onceListener, $eventName, $listener) {
            $this->off($eventName, $onceListener);

            \call_user_func_array($listener, \func_get_args());
        };

        $this->on($eventName, $onceListener, $priority);
    }

    /**
     * @deprecated
     */
    public function emit(string $eventName, $payload = null): void
    {
        if (null !== $payload && !($payload instanceof Event)) {
            $payload = new GenericEvent($payload);
        }

        $this->dispatcher->dispatch($eventName, $payload);
    }
}