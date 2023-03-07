<?php

namespace PE\Component\WAMP\Router;

use PE\Component\WAMP\SessionBaseInterface;

interface SessionInterface extends SessionBaseInterface
{
    /**
     * Get successfully used authentication method
     *
     * @return string|null
     */
    public function getAuthMethod(): ?string;

    /**
     * Set successfully used authentication method
     *
     * @param string $authMethod
     */
    public function setAuthMethod(string $authMethod): void;
}