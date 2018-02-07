<?php

namespace PE\Component\WAMP\Message;

use PE\Component\WAMP\MessageCode;

/**
 * <code>[ERROR, REQUEST.Type|int, REQUEST.Request|id, Details|dict, Error|uri]</code>
 * <code>[ERROR, REQUEST.Type|int, REQUEST.Request|id, Details|dict, Error|uri, Arguments|list]</code>
 * <code>[ERROR, REQUEST.Type|int, REQUEST.Request|id, Details|dict, Error|uri, Arguments|list, ArgumentsKw|dict]</code>
 */
class ErrorMessage extends Message
{
    use Details;
    use Arguments;

    /**
     * @var int
     */
    private $errorMessageCode;

    /**
     * @var int
     */
    private $errorRequestID;

    /**
     * @var string
     */
    private $errorURI;

    /**
     * @param int        $errorMessageCode
     * @param int        $errorRequestID
     * @param array      $details
     * @param string     $errorUri
     * @param array|null $arguments
     * @param array|null $argumentsKw
     */
    public function __construct(
        $errorMessageCode,
        $errorRequestID,
        array $details,
        $errorUri,
        array $arguments = null,
        array $argumentsKw = null
    ) {
        $this->setErrorMessageCode($errorMessageCode);
        $this->setErrorRequestID($errorRequestID);
        $this->setDetails($details);
        $this->setErrorURI($errorUri);
        $this->setArguments($arguments);
        $this->setArgumentsKw($argumentsKw);
    }

    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return MessageCode::_ERROR;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ERROR';
    }

    /**
     * @inheritDoc
     */
    public function getParts()
    {
        return array_merge(
            [$this->getErrorMessageCode(), $this->getErrorRequestID(), $this->getDetails(), $this->getErrorURI()],
            $this->getArgumentsParts()
        );
    }

    /**
     * @return int
     */
    public function getErrorMessageCode()
    {
        return $this->errorMessageCode;
    }

    /**
     * @param int $errorMessageCode
     *
     * @return self
     */
    public function setErrorMessageCode($errorMessageCode)
    {
        $this->errorMessageCode = (int) $errorMessageCode;
        return $this;
    }

    /**
     * @return int
     */
    public function getErrorRequestID()
    {
        return $this->errorRequestID;
    }

    /**
     * @param int $errorRequestID
     *
     * @return self
     */
    public function setErrorRequestID($errorRequestID)
    {
        $this->errorRequestID = (int) $errorRequestID;
        return $this;
    }

    /**
     * @return string
     */
    public function getErrorURI()
    {
        return $this->errorURI;
    }

    /**
     * @param string $errorURI
     *
     * @return self
     */
    public function setErrorURI($errorURI)
    {
        $this->errorURI = $errorURI;
        return $this;
    }
}