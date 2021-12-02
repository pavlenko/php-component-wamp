<?php

namespace PE\Component\WAMP\Message;

use PE\Component\WAMP\MessageCode;

/**
 * Call as originally issued by the Caller to the Dealer.
 *
 * <code>[CALL, Request|id, Options|dict, Procedure|uri]</code>
 * <code>[CALL, Request|id, Options|dict, Procedure|uri, Arguments|list]</code>
 * <code>[CALL, Request|id, Options|dict, Procedure|uri, Arguments|list, ArgumentsKw|dict]</code>
 */
final class CallMessage extends Message implements ActionInterface
{
    use RequestID;
    use Options;
    use Arguments;

    /**
     * @var string
     */
    private string $procedureURI;

    /**
     * @param int $requestID
     * @param array      $options
     * @param string $procedureURI
     * @param array|null $arguments
     * @param array|null $argumentsKw
     */
    public function __construct(
        int    $requestID,
        array  $options,
        string $procedureURI,
        array  $arguments = null,
        array  $argumentsKw = null
    ) {
        $this->setRequestID($requestID);
        $this->setOptions($options);
        $this->setProcedureURI($procedureURI);
        $this->setArguments($arguments);
        $this->setArgumentsKw($argumentsKw);
    }

    /**
     * @inheritDoc
     */
    public function getCode(): int
    {
        return MessageCode::_CALL;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'CALL';
    }

    /**
     * @inheritDoc
     */
    public function getParts(): array
    {
        return array_merge(
            [$this->getRequestID(), $this->getOptions(), $this->getProcedureURI()],
            $this->getArgumentsParts()
        );
    }

    /**
     * @inheritDoc
     */
    public function getActionUri(): string
    {
        return $this->getProcedureURI();
    }

    /**
     * @inheritDoc
     */
    public function getActionName(): string
    {
        return 'call';
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
     *
     * @return self
     */
    public function setProcedureURI(string $procedureURI): CallMessage
    {
        $this->procedureURI = (string) $procedureURI;
        return $this;
    }
}