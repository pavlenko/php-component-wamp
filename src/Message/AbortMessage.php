<?php

namespace PE\Component\WAMP\Message;

use PE\Component\WAMP\MessageCode;

/**
 * <code>[ABORT, Details|dict, Reason|uri]</code>
 */
class AbortMessage extends Message
{
    use Details;

    /**
     * @var string
     */
    private $responseUri;

    /**
     * @param array  $details
     * @param string $responseUri
     */
    public function __construct(array $details, $responseUri)
    {
        $this->setDetails($details);
        $this->setResponseUri($responseUri);
    }

    /**
     * @inheritDoc
     */
    public function getCode()
    {
       return MessageCode::_ABORT;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ABORT';
    }

    /**
     * @inheritDoc
     */
    public function getParts()
    {
        return [$this->getDetails(), $this->getResponseUri()];
    }

    /**
     * @return string
     */
    public function getResponseUri()
    {
        return $this->responseUri;
    }

    /**
     * @param string $responseUri
     *
     * @return self
     */
    public function setResponseUri($responseUri)
    {
        $this->responseUri = (string) $responseUri;
        return $this;
    }
}