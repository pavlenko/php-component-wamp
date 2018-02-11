<?php

namespace PE\Component\WAMP\Client;

class RegistrationCollection
{
    /**
     * @var Registration[]
     */
    private $registrations = [];

    /**
     * @param Registration $registration
     */
    public function add(Registration $registration)
    {
        $this->registrations[spl_object_hash($registration)] = $registration;
    }

    /**
     * @param Registration $registration
     */
    public function remove(Registration $registration)
    {
        if ($key = array_search($registration, $this->registrations, true)) {
            unset($this->registrations[$key]);
        }
    }

    /**
     * @param string $procedureURI
     *
     * @return Registration|null
     */
    public function findByProcedureURI($procedureURI)
    {
        $filtered = array_filter($this->registrations, function (Registration $registration) use ($procedureURI) {
            return $registration->getProcedureURI() === $procedureURI;
        });

        return current($filtered) ?: null;
    }

    /**
     * @param int $id
     *
     * @return Registration|null
     */
    public function findByRegisterRequestID($id)
    {
        $filtered = array_filter($this->registrations, function (Registration $registration) use ($id) {
            return $registration->getRegisterRequestID() === $id;
        });

        return current($filtered) ?: null;
    }

    /**
     * @param int $id
     *
     * @return Registration|null
     */
    public function findByUnregisterRequestID($id)
    {
        $filtered = array_filter($this->registrations, function (Registration $registration) use ($id) {
            return $registration->getUnregisterRequestID() === $id;
        });

        return current($filtered) ?: null;
    }

    /**
     * @param int $id
     *
     * @return Registration|null
     */
    public function findByRegistrationID($id)
    {
        $filtered = array_filter($this->registrations, function (Registration $registration) use ($id) {
            return $registration->getRegistrationID() === $id;
        });

        return current($filtered) ?: null;
    }
}