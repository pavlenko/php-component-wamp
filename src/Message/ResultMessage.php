<?php

namespace PE\Component\WAMP\Message;

use PE\Component\WAMP\MessageCode;

/**
 * Result of a call as returned by Dealer to Caller.
 *
 * <code>[RESULT, CALL.Request|id, Details|dict]</code>
 * <code>[RESULT, CALL.Request|id, Details|dict, YIELD.Arguments|list]</code>
 * <code>[RESULT, CALL.Request|id, Details|dict, YIELD.Arguments|list, YIELD.ArgumentsKw|dict]</code>
 */
class ResultMessage extends Message
{
    use RequestID;
    use Details;
    use Arguments;

    /**
     * @param int        $requestID
     * @param array      $details
     * @param array|null $arguments
     * @param array|null $argumentsKw
     */
    public function __construct($requestID, array $details, array $arguments = null, array $argumentsKw = null)
    {
        $this->setRequestID($requestID);
        $this->setDetails($details);
        $this->setArguments($arguments);
        $this->setArgumentsKw($argumentsKw);
    }

    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return MessageCode::_RESULT;
    }

    /**
     * @inheritDoc
     */
    public function getParts()
    {
        return array_merge(
            [$this->getRequestID(), $this->getDetails()],
            $this->getArgumentsParts()
        );
    }
}