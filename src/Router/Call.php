<?php

namespace PE\Component\WAMP\Router;

use PE\Component\WAMP\Message\CallMessage;
use PE\Component\WAMP\Message\CancelMessage;
use PE\Component\WAMP\Message\InterruptMessage;
use PE\Component\WAMP\Message\InvocationMessage;

class Call
{
    /**
     * @var Session
     */
    private $calleeSession;

    /**
     * @var Session
     */
    private $callerSession;

    /**
     * @var CallMessage
     */
    private $callMessage;

    /**
     * @var InvocationMessage
     */
    private $invocationMessage;

    /**
     * @var CancelMessage
     */
    private $cancelMessage;

    /**
     * @var InterruptMessage
     */
    private $interruptMessage;

    /**
     * @return Session
     */
    public function getCalleeSession()
    {
        return $this->calleeSession;
    }

    /**
     * @param Session $session
     */
    public function setCalleeSession(Session $session)
    {
        $this->calleeSession = $session;
    }

    /**
     * @return Session
     */
    public function getCallerSession()
    {
        return $this->callerSession;
    }

    /**
     * @param Session $session
     */
    public function setCallerSession(Session $session)
    {
        $this->callerSession = $session;
    }

    /**
     * @return CallMessage
     */
    public function getCallMessage()
    {
        return $this->callMessage;
    }

    /**
     * @param CallMessage $message
     */
    public function setCallMessage(CallMessage $message)
    {
        $this->callMessage = $message;
    }

    /**
     * @return InvocationMessage
     */
    public function getInvocationMessage()
    {
        return $this->invocationMessage;
    }

    /**
     * @param InvocationMessage $message
     */
    public function setInvocationMessage(InvocationMessage $message)
    {
        $this->invocationMessage = $message;
    }

    /**
     * @return CancelMessage
     */
    public function getCancelMessage()
    {
        return $this->cancelMessage;
    }

    /**
     * @param CancelMessage $message
     */
    public function setCancelMessage(CancelMessage $message)
    {
        $this->cancelMessage = $message;
    }

    /**
     * @return InterruptMessage
     */
    public function getInterruptMessage()
    {
        return $this->interruptMessage;
    }

    /**
     * @param InterruptMessage $message
     */
    public function setInterruptMessage(InterruptMessage $message)
    {
        $this->interruptMessage = $message;
    }
}