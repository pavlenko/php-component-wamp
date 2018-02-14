<?php

namespace PE\Component\WAMP\Client\Authentication\Method;

interface MethodInterface
{
    /**
     * @return string
     */
    public function getName();
}