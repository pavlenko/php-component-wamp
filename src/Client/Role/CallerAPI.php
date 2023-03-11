<?php

namespace PE\Component\WAMP\Client\Role;

use PE\Component\WAMP\Client\Call;
use PE\Component\WAMP\Client\Session\SessionInterface;
use PE\Component\WAMP\Message\CallMessage;
use PE\Component\WAMP\Message\CancelMessage;
use PE\Component\WAMP\Util;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;

final class CallerAPI
{
    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function call(string $procedureURI, array $arguments = [], array $argumentsKw = [], array $options = []): PromiseInterface
    {
        // For use progressive results set $options['receive_progress'] = true
        // For use timeouts set $options['timeout'] = N (positive integer)
        $requestID = Util::generateID();

        $deferred = new Deferred(function () use ($requestID) {
            // This is only one possible point to cancel a call
            $this->session->send(new CancelMessage($requestID, []));
        });

        $this->session->callRequests = $this->session->callRequests ?: [];
        $this->session->callRequests[] = new Call($requestID, $deferred);

        $this->session->send(new CallMessage($requestID, $options, $procedureURI, $arguments, $argumentsKw));

        return $deferred->promise();
    }
}