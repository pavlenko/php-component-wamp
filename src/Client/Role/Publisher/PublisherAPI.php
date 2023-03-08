<?php

namespace PE\Component\WAMP\Client\Role\Publisher;

use PE\Component\WAMP\Client\SessionInterface;
use PE\Component\WAMP\Message\PublishMessage;
use PE\Component\WAMP\Util;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use function React\Promise\resolve;

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
        $deferred  = null;

        if (isset($options['acknowledge']) && true === $options['acknowledge']) {
            if (!is_array($this->session->publishRequests)) {
                $this->session->publishRequests = [];
            }

            $this->session->publishRequests[$requestID] = $deferred = new Deferred();
        }

        $this->session->send(new PublishMessage($requestID, $options, $topic, $arguments, $argumentsKw));

        return $deferred ? $deferred->promise() : resolve();
    }
}
