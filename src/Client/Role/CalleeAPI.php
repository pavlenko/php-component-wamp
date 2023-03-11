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

        $requestID = Util::generateID();
        $deferred  = new Deferred();

        $registration = new Registration($procedureURI, $callback);
        $registration->setRegisterRequestID($requestID);
        $registration->setRegisterDeferred($deferred);

        $this->session->registrations[$procedureURI] = $registration;

        // If supported pattern_based_registration feature you may send $option['match'] = 'prefix'|'wildcard'
        // If supported shared_registration feature you may send $options['invoke'] =  'single'|'roundrobin'|'random'|'first'|'last'
        $this->session->send(new RegisterMessage($requestID, $options, $procedureURI));

        return $deferred->promise();
    }

    public function unregister(string $procedureURI): PromiseInterface
    {
        $this->session->registrations = $this->session->registrations ?: [];
        foreach ($this->session->registrations as $registration) {
            if ($registration->getProcedureURI() === $procedureURI) {
                $registration->getRegisterDeferred()->reject();

                $requestID = Util::generateID();
                $deferred  = new Deferred();

                $registration->setUnregisterRequestID($requestID);
                $registration->setUnregisterDeferred($deferred);

                $this->session->send(new UnregisterMessage($requestID, $registration->getRegistrationID()));

                return $deferred->promise();
            }
        }

        return reject();
    }
}