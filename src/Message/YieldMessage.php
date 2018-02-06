<?php

namespace PE\Component\WAMP\Message;

use PE\Component\WAMP\MessageCode;

/**
 * Actual yield from an endpoint sent by a Callee to Dealer.
 *
 * <code>[YIELD, INVOCATION.Request|id, Options|dict]</code>
 * <code>[YIELD, INVOCATION.Request|id, Options|dict, Arguments|list]</code>
 * <code>[YIELD, INVOCATION.Request|id, Options|dict, Arguments|list, ArgumentsKw|dict]</code>
 */
class YieldMessage extends Message
{
    use RequestID;
    use Options;
    use Arguments;

    /**
     * @param int        $requestID
     * @param array      $options
     * @param array|null $arguments
     * @param array|null $argumentsKw
     */
    public function __construct($requestID, array $options, array $arguments = null, array $argumentsKw = null)
    {
        $this->setRequestID($requestID);
        $this->setOptions($options);
        $this->setArguments($arguments);
        $this->setArgumentsKw($argumentsKw);
    }

    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return MessageCode::_YIELD;
    }

    /**
     * @inheritDoc
     */
    public function getParts()
    {
        return array_merge(
            [$this->getRequestID(), $this->getOptions()],
            $this->getArgumentsParts()
        );
    }
}