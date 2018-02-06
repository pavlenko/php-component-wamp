<?php

namespace PE\Component\WAMP\EventDispatcher;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

trait EventDispatcherTrait
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @return EventDispatcherInterface
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param string   $eventName
     * @param callable $listener
     * @param int      $priority
     */
    public function on($eventName, callable $listener, $priority = 0)
    {
        $this->dispatcher->addListener($eventName, $listener, $priority);
    }

    /**
     * @param string   $eventName
     * @param callable $listener
     */
    public function off($eventName, callable $listener)
    {
        $this->dispatcher->removeListener($eventName, $listener);
    }

    /**
     * @param string   $eventName
     * @param callable $listener
     * @param int      $priority
     */
    public function once($eventName, callable $listener, $priority = 0)
    {
        $onceListener = function () use (&$onceListener, $eventName, $listener) {
            $this->off($eventName, $onceListener);

            \call_user_func_array($listener, \func_get_args());
        };

        $this->on($eventName, $onceListener, $priority);
    }

    /**
     * @param string $eventName
     * @param mixed  $payload
     */
    public function emit($eventName, $payload = null)
    {
        if (null !== $payload && !($payload instanceof Event)) {
            $payload = new GenericEvent($payload);
        }

        $this->dispatcher->dispatch($eventName, $payload);
    }
}