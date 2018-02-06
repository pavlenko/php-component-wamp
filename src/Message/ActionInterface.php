<?php

namespace PE\Component\WAMP\Message;

interface ActionInterface
{
    /**
     * Get action Uri so that the authorization manager doesn't have to know exactly the type of object to get Uri
     *
     * @return string
     */
    public function getActionUri();

    /**
     * Get action name: "publish", "subscribe", "register", "call"
     *
     * @return string
     */
    public function getActionName();
}