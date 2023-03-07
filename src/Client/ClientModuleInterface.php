<?php

namespace PE\Component\WAMP\Client;

interface ClientModuleInterface
{
    /**
     * Attach module to client instance
     *
     * @param Client $client
     */
    public function attach(Client $client): void;

    /**
     * Detach module from client instance
     *
     * @param Client $client
     */
    public function detach(Client $client): void;
}