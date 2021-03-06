<?php

namespace PE\Component\WAMP\Client\Role;

use PE\Component\WAMP\Client\Call;
use PE\Component\WAMP\Client\CallCollection;
use PE\Component\WAMP\Message\CallMessage;
use PE\Component\WAMP\Message\CancelMessage;
use PE\Component\WAMP\Session;
use PE\Component\WAMP\Util;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;

class CallerAPI
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @param string $procedureURI
     * @param array  $arguments
     * @param array  $argumentsKw
     * @param array  $options
     *
     * @return PromiseInterface
     */
    public function call($procedureURI, array $arguments = [], array $argumentsKw = [], array $options = [])
    {
        $requestID = Util::generateID();

        $deferred = new Deferred(function () use ($requestID) {
            // This is only one possible point to cancel a call
            $this->session->send(new CancelMessage($requestID, []));
        });

        if (!($this->session->callRequests instanceof CallCollection)) {
            $this->session->callRequests = new CallCollection();
        }

        $this->session->callRequests->add(new Call($requestID, $deferred));

        $this->session->send(new CallMessage($requestID, $options ?: [], $procedureURI, $arguments, $argumentsKw));

        return $deferred->promise();
    }
}