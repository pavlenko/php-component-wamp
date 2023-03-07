<?php

namespace PE\Component\WAMP\Client;

use PE\Component\WAMP\Util\EventsInterface;

interface ClientModuleInterface
{
    /**
     * Attach module to client instance
     *
     * @param EventsInterface $events
     */
    public function attach(EventsInterface $events): void;

    /**
     * Detach module from client instance
     *
     * @param EventsInterface $events
     */
    public function detach(EventsInterface $events): void;
}