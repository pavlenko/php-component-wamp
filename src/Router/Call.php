<?php

namespace PE\Component\WAMP\Router;

use PE\Component\WAMP\Message\CallMessage;
use PE\Component\WAMP\Message\CancelMessage;
use PE\Component\WAMP\Message\InterruptMessage;
use PE\Component\WAMP\Message\InvocationMessage;
use PE\Component\WAMP\Router\Session\SessionInterface;

final class Call
{
    private SessionInterface $calleeSession;

    private SessionInterface $callerSession;

    private CallMessage $callMessage;

    private InvocationMessage $invocationMessage;

    private CancelMessage $cancelMessage;

    private InterruptMessage $interruptMessage;

    public function getCalleeSession(): SessionInterface
    {
        return $this->calleeSession;
    }

    public function setCalleeSession(SessionInterface $session): void
    {
        $this->calleeSession = $session;
    }

    public function getCallerSession(): SessionInterface
    {
        return $this->callerSession;
    }

    public function setCallerSession(SessionInterface $session): void
    {
        $this->callerSession = $session;
    }

    public function getCallMessage(): CallMessage
    {
        return $this->callMessage;
    }

    public function setCallMessage(CallMessage $message): void
    {
        $this->callMessage = $message;
    }

    public function getInvocationMessage(): InvocationMessage
    {
        return $this->invocationMessage;
    }

    public function setInvocationMessage(InvocationMessage $message): void
    {
        $this->invocationMessage = $message;
    }

    public function getCancelMessage(): CancelMessage
    {
        return $this->cancelMessage;
    }

    public function setCancelMessage(CancelMessage $message): void
    {
        $this->cancelMessage = $message;
    }

    public function getInterruptMessage(): InterruptMessage
    {
        return $this->interruptMessage;
    }

    public function setInterruptMessage(InterruptMessage $message): void
    {
        $this->interruptMessage = $message;
    }
}
