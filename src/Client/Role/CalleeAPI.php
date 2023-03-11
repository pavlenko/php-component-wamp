<?php

namespace PE\Component\WAMP\Client\Role;

use PE\Component\WAMP\Client\Registration;
use PE\Component\WAMP\Client\SessionInterface;
use PE\Component\WAMP\Message\RegisterMessage;
use PE\Component\WAMP\Message\UnregisterMessage;
use PE\Component\WAMP\Util;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use function React\Promise\reject;

final class CalleeAPI
{
    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function register(string $procedureURI, \Closure $callback, array $options = []): PromiseInterface
    {
        $this->session->registrations = $this->session->registrations ?: [];
        foreach ($this->session->registrations as $registration) {
            if ($registration->getProcedureURI() === $procedureURI) {
                throw new \InvalidArgumentException(sprintf(
                    'Procedure with uri "%s" already registered',
                    $procedureURI
                ));
            }
        }

        $requestId = Util::generateID();

        $registration = new Registration($procedureURI, $callback);
        $registration->setRegisterRequestID($requestId);
        $registration->setRegisterDeferred($deferred = new Deferred());

        $this->session->registrations[$procedureURI] = $registration;

        $this->session->send(new RegisterMessage($requestId, $options, $procedureURI));

        return $deferred->promise();
    }

    public function unregister(string $procedureURI): PromiseInterface
    {
        $this->session->registrations = $this->session->registrations ?: [];
        foreach ($this->session->registrations as $registration) {
            if ($registration->getProcedureURI() === $procedureURI) {
                $registration->getRegisterDeferred()->reject();

                $requestID = Util::generateID();
                $registration->setUnregisterRequestID($requestID);
                $registration->setUnregisterDeferred($deferred = new Deferred());

                $this->session->send(new UnregisterMessage($requestID, $registration->getRegistrationID()));

                return $deferred->promise();
            }
        }

        return reject();
    }
}