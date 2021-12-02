<?php

namespace PE\Component\WAMP\Client;

use React\Promise\Deferred;

class Registration
{
    /**
     * @var string
     */
    private string $procedureURI;

    /**
     * @var callable
     */
    private $callback;

    /**
     * @var int
     */
    private int $registrationID;

    /**
     * @var int
     */
    private int $registerRequestID;

    /**
     * @var int
     */
    private int $unregisterRequestID;

    /**
     * @var Deferred
     */
    private Deferred $registerDeferred;

    /**
     * @var Deferred
     */
    private Deferred $unregisterDeferred;

    public function __construct(int $procedureURI, callable $callback)
    {
        $this->procedureURI = $procedureURI;
        $this->callback     = $callback;
    }

    /**
     * @return string
     */
    public function getProcedureURI(): string
    {
        return $this->procedureURI;
    }

    /**
     * @param string $procedureURI
     */
    public function setProcedureURI(string $procedureURI): void
    {
        $this->procedureURI = $procedureURI;
    }

    /**
     * @return callable
     */
    public function getCallback(): callable
    {
        return $this->callback;
    }

    /**
     * @param callable $callback
     */
    public function setCallback(callable $callback): void
    {
        $this->callback = $callback;
    }

    /**
     * @return int
     */
    public function getRegistrationID(): int
    {
        return $this->registrationID;
    }

    /**
     * @param int $registrationID
     */
    public function setRegistrationID(int $registrationID): void
    {
        $this->registrationID = (int) $registrationID;
    }

    /**
     * @return int
     */
    public function getRegisterRequestID(): int
    {
        return $this->registerRequestID;
    }

    /**
     * @param int $registerRequestID
     */
    public function setRegisterRequestID(int $registerRequestID): void
    {
        $this->registerRequestID = (int) $registerRequestID;
    }

    /**
     * @return int
     */
    public function getUnregisterRequestID(): int
    {
        return $this->unregisterRequestID;
    }

    /**
     * @param int $unregisterRequestID
     */
    public function setUnregisterRequestID(int $unregisterRequestID): void
    {
        $this->unregisterRequestID = (int) $unregisterRequestID;
    }

    /**
     * @return Deferred
     */
    public function getRegisterDeferred(): Deferred
    {
        return $this->registerDeferred;
    }

    /**
     * @param Deferred $deferred
     */
    public function setRegisterDeferred(Deferred $deferred): void
    {
        $this->registerDeferred = $deferred;
    }

    /**
     * @return Deferred
     */
    public function getUnregisterDeferred(): Deferred
    {
        return $this->unregisterDeferred;
    }

    /**
     * @param Deferred $deferred
     */
    public function setUnregisterDeferred(Deferred $deferred): void
    {
        $this->unregisterDeferred = $deferred;
    }
}