<?php

namespace PE\Component\WAMP\Client;

final class RegistrationCollection
{
    /**
     * @var Registration[]
     */
    private array $registrations = [];

    public function add(Registration $registration)
    {
        $this->registrations[spl_object_hash($registration)] = $registration;
    }

    public function remove(Registration $registration)
    {
        if ($key = array_search($registration, $this->registrations, true)) {
            unset($this->registrations[$key]);
        }
    }

    public function findByProcedureURI(string $procedureURI): ?Registration
    {
        $filtered = array_filter($this->registrations, function (Registration $registration) use ($procedureURI) {
            return $registration->getProcedureURI() === $procedureURI;
        });

        return current($filtered) ?: null;
    }

    public function findByRegisterRequestID(int $id): ?Registration
    {
        $filtered = array_filter($this->registrations, function (Registration $registration) use ($id) {
            return $registration->getRegisterRequestID() === $id;
        });

        return current($filtered) ?: null;
    }

    public function findByUnregisterRequestID(int $id): ?Registration
    {
        $filtered = array_filter($this->registrations, function (Registration $registration) use ($id) {
            return $registration->getUnregisterRequestID() === $id;
        });

        return current($filtered) ?: null;
    }

    public function findByRegistrationID(int $id): ?Registration
    {
        $filtered = array_filter($this->registrations, function (Registration $registration) use ($id) {
            return $registration->getRegistrationID() === $id;
        });

        return current($filtered) ?: null;
    }
}