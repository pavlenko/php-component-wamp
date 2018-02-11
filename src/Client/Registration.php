<?php

namespace PE\Component\WAMP\Client;

use React\Promise\Deferred;

class Registration
{
    /**
     * @var int
     */
    private $procedureURI;

    /**
     * @var callable
     */
    private $callback;

    /**
     * @var int
     */
    private $registrationID;

    /**
     * @var int
     */
    private $registerRequestID;

    /**
     * @var int
     */
    private $unregisterRequestID;

    /**
     * @var Deferred
     */
    private $registerDeferred;

    /**
     * @var Deferred
     */
    private $unregisterDeferred;

    public function __construct($procedureURI, callable $callback)
    {
        $this->procedureURI = $procedureURI;
        $this->callback     = $callback;
    }

    /**
     * @return int
     */
    public function getProcedureURI()
    {
        return $this->procedureURI;
    }

    /**
     * @param int $procedureURI
     */
    public function setProcedureURI($procedureURI)
    {
        $this->procedureURI = $procedureURI;
    }

    /**
     * @return callable
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @param callable $callback
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
    }

    /**
     * @return int
     */
    public function getRegistrationID()
    {
        return $this->registrationID;
    }

    /**
     * @param int $registrationID
     */
    public function setRegistrationID($registrationID)
    {
        $this->registrationID = (int) $registrationID;
    }

    /**
     * @return int
     */
    public function getRegisterRequestID()
    {
        return $this->registerRequestID;
    }

    /**
     * @param int $registerRequestID
     */
    public function setRegisterRequestID($registerRequestID)
    {
        $this->registerRequestID = (int) $registerRequestID;
    }

    /**
     * @return int
     */
    public function getUnregisterRequestID()
    {
        return $this->unregisterRequestID;
    }

    /**
     * @param int $unregisterRequestID
     */
    public function setUnregisterRequestID($unregisterRequestID)
    {
        $this->unregisterRequestID = (int) $unregisterRequestID;
    }

    /**
     * @return Deferred
     */
    public function getRegisterDeferred()
    {
        return $this->registerDeferred;
    }

    /**
     * @param Deferred $deferred
     */
    public function setRegisterDeferred(Deferred $deferred)
    {
        $this->registerDeferred = $deferred;
    }

    /**
     * @return Deferred
     */
    public function getUnregisterDeferred()
    {
        return $this->unregisterDeferred;
    }

    /**
     * @param Deferred $deferred
     */
    public function setUnregisterDeferred(Deferred $deferred)
    {
        $this->unregisterDeferred = $deferred;
    }
}