<?php

namespace PE\Component\WAMP\Message;

/**
 * Actual yield from an endpoint sent by a Callee to Dealer.
 *
 * <code>[YIELD, INVOCATION.Request|id, Options|dict]</code>
 * <code>[YIELD, INVOCATION.Request|id, Options|dict, Arguments|list]</code>
 * <code>[YIELD, INVOCATION.Request|id, Options|dict, Arguments|list, ArgumentsKw|dict]</code>
 */
final class YieldMessage extends Message
{
    use RequestID;
    use Options;
    use Arguments;

    /**
     * @param int $requestID
     * @param array      $options
     * @param array|null $arguments
     * @param array|null $argumentsKw
     */
    public function __construct(int $requestID, array $options, array $arguments = null, array $argumentsKw = null)
    {
        $this->setRequestID($requestID);
        $this->setOptions($options);
        $this->setArguments($arguments);
        $this->setArgumentsKw($argumentsKw);
    }

    /**
     * @inheritDoc
     */
    public function getCode(): int
    {
        return self::CODE_YIELD;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'YIELD';
    }

    /**
     * @inheritDoc
     */
    public function getParts(): array
    {
        return array_merge(
            [$this->getRequestID(), $this->getOptions()],
            $this->getArgumentsParts()
        );
    }
}
