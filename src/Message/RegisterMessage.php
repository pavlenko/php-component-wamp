<?php

namespace PE\Component\WAMP\Message;

use PE\Component\WAMP\MessageCode;

/**
 * A Callees request to register an endpoint at a Dealer.
 *
 * <code>[REGISTER, Request|id, Options|dict, Procedure|uri]</code>
 */
final class RegisterMessage extends Message implements ActionInterface
{
    use RequestID;
    use Options;

    /**
     * @var string
     */
    private string $procedureURI;

    /**
     * @param int $requestID
     * @param array  $options
     * @param string $procedureURI
     */
    public function __construct(int $requestID, array $options, string $procedureURI)
    {
        $this->setRequestID($requestID);
        $this->setOptions($options);
        $this->setProcedureURI($procedureURI);
    }

    /**
     * @inheritDoc
     */
    public function getCode(): int
    {
        return MessageCode::_REGISTER;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'REGISTER';
    }

    /**
     * @inheritDoc
     */
    public function getParts(): array
    {
        return [$this->getRequestID(), $this->getOptions(), $this->getProcedureURI()];
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
        return 'register';
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
    public function setProcedureURI(string $procedureURI): RegisterMessage
    {
        $this->procedureURI = $procedureURI;
        return $this;
    }
}
