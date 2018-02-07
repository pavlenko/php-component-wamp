<?php

namespace PE\Component\WAMP\Message;

use PE\Component\WAMP\MessageCode;

/**
 * Actual invocation of an endpoint sent by Dealer to a Callee.
 *
 * <code>[INVOCATION, Request|id, REGISTERED.Registration|id, Details|dict]</code>
 * <code>[INVOCATION, Request|id, REGISTERED.Registration|id, Details|dict, CALL.Arguments|list]</code>
 * <code>[INVOCATION, Request|id, REGISTERED.Registration|id, Details|dict, CALL.Arguments|list, CALL.ArgumentsKw|dict]</code>
 */
class InvocationMessage extends Message
{
    use RequestID;
    use Details;
    use Arguments;

    /**
     * @var int
     */
    private $registrationID;

    /**
     * @param int        $requestID
     * @param int        $registrationID
     * @param array      $details
     * @param array|null $arguments
     * @param array|null $argumentsKw
     */
    public function __construct($requestID, $registrationID, array $details, array $arguments = null, array $argumentsKw = null)
    {
        $this->setRequestID($requestID);
        $this->setRegistrationID($registrationID);
        $this->setDetails($details);
        $this->setArguments($arguments);
        $this->setArgumentsKw($argumentsKw);
    }

    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return MessageCode::_INVOCATION;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'INVOCATION';
    }

    /**
     * @inheritDoc
     */
    public function getParts()
    {
        return array_merge(
            [$this->getRequestID(), $this->getRegistrationID(), $this->getDetails()],
            $this->getArgumentsParts()
        );
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
     *
     * @return self
     */
    public function setRegistrationID($registrationID)
    {
        $this->registrationID = (int) $registrationID;
        return $this;
    }
}