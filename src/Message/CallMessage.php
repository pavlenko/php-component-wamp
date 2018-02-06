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
class CallMessage extends Message implements ActionInterface
{
    use RequestID;
    use Options;
    use Arguments;

    /**
     * @var string
     */
    private $procedureURI;

    /**
     * @param int        $requestID
     * @param array      $options
     * @param string     $procedureURI
     * @param array|null $arguments
     * @param array|null $argumentsKw
     */
    public function __construct(
        $requestID,
        array $options,
        $procedureURI,
        array $arguments = null,
        array $argumentsKw = null
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
    public function getCode()
    {
        return MessageCode::_CALL;
    }

    /**
     * @inheritDoc
     */
    public function getParts()
    {
        return array_merge(
            [$this->getRequestID(), $this->getOptions(), $this->getProcedureURI()],
            $this->getArgumentsParts()
        );
    }

    /**
     * @inheritDoc
     */
    public function getActionUri()
    {
        return $this->getProcedureURI();
    }

    /**
     * @inheritDoc
     */
    public function getActionName()
    {
        return 'call';
    }

    /**
     * @return string
     */
    public function getProcedureURI()
    {
        return $this->procedureURI;
    }

    /**
     * @param string $procedureURI
     *
     * @return self
     */
    public function setProcedureURI($procedureURI)
    {
        $this->procedureURI = (string) $procedureURI;
        return $this;
    }
}