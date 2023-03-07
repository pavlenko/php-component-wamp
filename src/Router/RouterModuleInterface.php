<?php

namespace PE\Component\WAMP\Router;

interface RouterModuleInterface
{
    /**
     * Attach module to router instance
     *
     * @param Router $router
     */
    public function attach(Router $router): void;

    /**
     * Detach module from router instance
     *
     * @param Router $router
     */
    public function detach(Router $router): void;
}