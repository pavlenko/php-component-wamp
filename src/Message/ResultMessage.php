<?php

namespace PE\Component\WAMP\Message;

/**
 * Result of a call as returned by Dealer to Caller.
 *
 * <code>[RESULT, CALL.Request|id, Details|dict]</code>
 * <code>[RESULT, CALL.Request|id, Details|dict, YIELD.Arguments|list]</code>
 * <code>[RESULT, CALL.Request|id, Details|dict, YIELD.Arguments|list, YIELD.ArgumentsKw|dict]</code>
 *
 * @codeCoverageIgnore
 */
final class ResultMessage extends Message
{
    use RequestID;
    use Details;
    use Arguments;

    /**
     * @param int $requestID
     * @param array      $details
     * @param array|null $arguments
     * @param array|null $argumentsKw
     */
    public function __construct(int $requestID, array $details, array $arguments = null, array $argumentsKw = null)
    {
        $this->setRequestID($requestID);
        $this->setDetails($details);
        $this->setArguments($arguments);
        $this->setArgumentsKw($argumentsKw);
    }

    /**
     * @inheritDoc
     */
    public function getCode(): int
    {
        return self::CODE_RESULT;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'RESULT';
    }

    /**
     * @inheritDoc
     */
    public function getParts(): array
    {
        return array_merge(
            [$this->getRequestID(), $this->getDetails()],
            $this->getArgumentsParts()
        );
    }
}
