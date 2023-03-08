<?php

namespace PE\Component\WAMP\Client\Role;

use PE\Component\WAMP\Client\Registration;
use PE\Component\WAMP\Client\RegistrationCollection;
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
        if (!($this->session->registrations instanceof RegistrationCollection)) {
            $this->session->registrations = new RegistrationCollection();
        }

        if ($this->session->registrations->findByProcedureURI($procedureURI)) {
            throw new \InvalidArgumentException(sprintf('Procedure with uri "%s" already registered', $procedureURI));
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
        $requestID     = Util::generateID();
        $registrations = $this->session->registrations ?: new RegistrationCollection();

        if ($registration = $registrations->findByProcedureURI($procedureURI)) {
            $registration->getRegisterDeferred()->reject();

            $registration->setUnregisterRequestID($requestID);
            $registration->setUnregisterDeferred($deferred = new Deferred());

            $this->session->send(new UnregisterMessage($requestID, $registration->getRegistrationID()));

            return $deferred->promise();
        }

        return reject();
    }
}