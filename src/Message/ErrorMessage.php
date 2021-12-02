<?php

namespace PE\Component\WAMP\Message;

use PE\Component\WAMP\MessageCode;

/**
 * <code>[ERROR, REQUEST.Type|int, REQUEST.Request|id, Details|dict, Error|uri]</code>
 * <code>[ERROR, REQUEST.Type|int, REQUEST.Request|id, Details|dict, Error|uri, Arguments|list]</code>
 * <code>[ERROR, REQUEST.Type|int, REQUEST.Request|id, Details|dict, Error|uri, Arguments|list, ArgumentsKw|dict]</code>
 */
final class ErrorMessage extends Message
{
    use Details;
    use Arguments;

    /**
     * @var int
     */
    private int $errorMessageCode;

    /**
     * @var int
     */
    private int $errorRequestID;

    /**
     * @var string
     */
    private string $errorURI;

    /**
     * @param int $errorMessageCode
     * @param int $errorRequestID
     * @param array      $details
     * @param string $errorUri
     * @param array|null $arguments
     * @param array|null $argumentsKw
     */
    public function __construct(
        int    $errorMessageCode,
        int    $errorRequestID,
        array  $details,
        string $errorUri,
        array  $arguments = null,
        array  $argumentsKw = null
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
    public function getCode(): int
    {
        return MessageCode::_ERROR;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'ERROR';
    }

    /**
     * @inheritDoc
     */
    public function getParts(): array
    {
        return array_merge(
            [$this->getErrorMessageCode(), $this->getErrorRequestID(), $this->getDetails(), $this->getErrorURI()],
            $this->getArgumentsParts()
        );
    }

    /**
     * @return int
     */
    public function getErrorMessageCode(): int
    {
        return $this->errorMessageCode;
    }

    /**
     * @param int $errorMessageCode
     *
     * @return self
     */
    public function setErrorMessageCode(int $errorMessageCode): ErrorMessage
    {
        $this->errorMessageCode = $errorMessageCode;
        return $this;
    }

    /**
     * @return int
     */
    public function getErrorRequestID(): int
    {
        return $this->errorRequestID;
    }

    /**
     * @param int $errorRequestID
     *
     * @return self
     */
    public function setErrorRequestID(int $errorRequestID): ErrorMessage
    {
        $this->errorRequestID = $errorRequestID;
        return $this;
    }

    /**
     * @return string
     */
    public function getErrorURI(): string
    {
        return $this->errorURI;
    }

    /**
     * @param string $errorURI
     *
     * @return self
     */
    public function setErrorURI(string $errorURI): ErrorMessage
    {
        $this->errorURI = $errorURI;
        return $this;
    }
}
