<?php

namespace PE\Component\WAMP\Client;

interface ClientModuleInterface
{
    public function subscribe(Client $client): void;

     public function unsubscribe(Client $client): void;
}