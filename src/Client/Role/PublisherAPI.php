<?php

namespace PE\Component\WAMP\Client\Role;

use PE\Component\WAMP\Message\PublishMessage;
use PE\Component\WAMP\Session;
use PE\Component\WAMP\Util;
use React\Promise\Deferred;
use React\Promise\FulfilledPromise;
use React\Promise\PromiseInterface;

class PublisherAPI
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
     * @param string $topic
     * @param array  $arguments
     * @param array  $argumentsKw
     * @param array  $options
     *
     * @return PromiseInterface
     *
     * @throws \InvalidArgumentException
     */
    public function publish($topic, array $arguments = [], array $argumentsKw = [], array $options = [])
    {
        $requestID = Util::generateID();
        $deferred  = null;

        if (isset($options['acknowledge']) && true === $options['acknowledge']) {
            if (!is_array($this->session->publishRequests)) {
                $this->session->publishRequests = [];
            }

            $this->session->publishRequests[$requestID] = $deferred = new Deferred();
        }

        //TODO process publisher_exclusion here

        $this->session->send(new PublishMessage($requestID, $options, $topic, $arguments, $argumentsKw));

        return $deferred ? $deferred->promise() : new FulfilledPromise();
    }
}
