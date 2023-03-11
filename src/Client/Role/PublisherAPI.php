<?php

namespace PE\Component\WAMP\Client\Role;

use PE\Component\WAMP\Client\SessionInterface;
use PE\Component\WAMP\Message\PublishMessage;
use PE\Component\WAMP\Util;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;

final class PublisherAPI
{
    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function publish(string $topic, array $arguments = [], array $argumentsKw = [], array $options = []): PromiseInterface
    {
        $requestID = Util::generateID();
        $deferred  = new Deferred();

        if (isset($options['acknowledge']) && true === $options['acknowledge']) {
            $this->session->publishRequests = $this->session->publishRequests ?: [];
            $this->session->publishRequests[$requestID] = $deferred;
        }

        $this->session->send(new PublishMessage($requestID, $options, $topic, $arguments, $argumentsKw));

        return $deferred->promise();
    }
}
