<?php

namespace PE\Component\WAMP\Router;

interface RouterModuleInterface
{
    public function subscribe(Router $router): void;

    public function unsubscribe(Router $router): void;
}