<?php

namespace PE\Component\WAMP\Client;

use React\Promise\Deferred;

/**
 * @codeCoverageIgnore
 */
final class Registration
{
    private string $procedureURI;
    private \Closure $callback;
    private int $registrationID = 0;
    private int $registerRequestID = 0;
    private int $unregisterRequestID = 0;
    private ?Deferred $registerDeferred = null;
    private ?Deferred $unregisterDeferred = null;

    public function __construct(string $procedureURI, \Closure $callback)
    {
        $this->procedureURI = $procedureURI;
        $this->callback     = $callback;
    }

    public function getProcedureURI(): string
    {
        return $this->procedureURI;
    }

    public function getCallback(): \Closure
    {
        return $this->callback;
    }

    public function getRegistrationID(): int
    {
        return $this->registrationID;
    }

    public function setRegistrationID(int $registrationID): void
    {
        $this->registrationID = $registrationID;
    }

    public function getRegisterRequestID(): int
    {
        return $this->registerRequestID;
    }

    public function setRegisterRequestID(int $registerRequestID): void
    {
        $this->registerRequestID = $registerRequestID;
    }

    public function getUnregisterRequestID(): int
    {
        return $this->unregisterRequestID;
    }

    public function setUnregisterRequestID(int $unregisterRequestID): void
    {
        $this->unregisterRequestID = $unregisterRequestID;
    }

    public function getRegisterDeferred(): ?Deferred
    {
        return $this->registerDeferred;
    }

    public function setRegisterDeferred(Deferred $deferred): void
    {
        $this->registerDeferred = $deferred;
    }

    public function getUnregisterDeferred(): ?Deferred
    {
        return $this->unregisterDeferred;
    }

    public function setUnregisterDeferred(Deferred $deferred): void
    {
        $this->unregisterDeferred = $deferred;
    }
}