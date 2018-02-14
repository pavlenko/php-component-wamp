<?php

namespace PE\Component\WAMP\Client\Authentication\Method;

class TicketMethod implements MethodInterface
{
    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'ticket';
    }
}