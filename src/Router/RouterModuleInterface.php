<?php

namespace PE\Component\WAMP\Router;

use PE\Component\WAMP\Util\EventsInterface;

interface RouterModuleInterface
{
    /**
     * Attach module to router instance
     *
     * @param EventsInterface $events
     */
    public function attach(EventsInterface $events): void;

    /**
     * Detach module from router instance
     *
     * @param EventsInterface $events
     */
    public function detach(EventsInterface $events): void;
}