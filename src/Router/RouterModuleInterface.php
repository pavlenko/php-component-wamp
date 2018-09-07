<?php

namespace PE\Component\WAMP\Router;

interface RouterModuleInterface
{
    /**
     * @param Router $router
     */
    public function subscribe(Router $router);

    /**
     * @param Router $router
     */
    public function unsubscribe(Router $router);
}