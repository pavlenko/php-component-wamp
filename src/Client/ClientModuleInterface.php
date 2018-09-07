<?php

namespace PE\Component\WAMP\Client;

interface ClientModuleInterface
{
    /**
     * @param Client $client
     */
    public function subscribe(Client $client);

    /**
     * @param Client $client
     */
    public function unsubscribe(Client $client);
}