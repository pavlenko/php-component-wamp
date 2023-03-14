<?php

namespace PE\Component\WAMP\Router\DTO;

use PE\Component\WAMP\Router\Session\SessionInterface;

/**
 * @codeCoverageIgnore
 */
final class Registration
{
    private SessionInterface $session;
    private string $procedureURI;
    private int $registrationID;

    public function __construct(SessionInterface $session, string $procedureURI, int $registrationID)
    {
        $this->session        = $session;
        $this->procedureURI   = $procedureURI;
        $this->registrationID = $registrationID;
    }

    public function getSession(): SessionInterface
    {
        return $this->session;
    }

    public function getProcedureURI(): string
    {
        return $this->procedureURI;
    }

    public function getRegistrationID(): int
    {
        return $this->registrationID;
    }
}