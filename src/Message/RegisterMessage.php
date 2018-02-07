<?php

namespace PE\Component\WAMP\Message;

use PE\Component\WAMP\MessageCode;

/**
 * A Callees request to register an endpoint at a Dealer.
 *
 * <code>[REGISTER, Request|id, Options|dict, Procedure|uri]</code>
 */
class RegisterMessage extends Message implements ActionInterface
{
    use RequestID;
    use Options;

    /**
     * @var string
     */
    private $procedureURI;

    /**
     * @param int    $requestID
     * @param array  $options
     * @param string $procedureURI
     */
    public function __construct($requestID, array $options, $procedureURI)
    {
        $this->setRequestID($requestID);
        $this->setOptions($options);
        $this->setProcedureURI($procedureURI);
    }

    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return MessageCode::_REGISTER;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'REGISTER';
    }

    /**
     * @inheritDoc
     */
    public function getParts()
    {
        return [$this->getRequestID(), $this->getOptions(), $this->getProcedureURI()];
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
        return 'register';
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