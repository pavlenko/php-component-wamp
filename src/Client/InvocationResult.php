<?php

namespace PE\Component\WAMP\Client;

use PE\Component\WAMP\Message\Arguments;

class InvocationResult
{
    use Arguments;

    /**
     * @var callable
     */
    private $canceller;

    /**
     * @return callable
     */
    public function getCanceller()
    {
        return $this->canceller;
    }

    /**
     * @param callable $canceller
     *
     * @return self
     */
    public function setCanceller($canceller)
    {
        $this->canceller = $canceller;
        return $this;
    }
}