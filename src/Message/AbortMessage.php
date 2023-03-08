<?php

namespace PE\Component\WAMP\Message;

/**
 * <code>[ABORT, Details|dict, Reason|uri]</code>
 */
final class AbortMessage extends Message
{
    use Details;

    /**
     * @var string
     */
    private string $responseUri;

    /**
     * @param array  $details
     * @param string $responseUri
     */
    public function __construct(array $details, string $responseUri)
    {
        $this->setDetails($details);
        $this->setResponseUri($responseUri);
    }

    public function getCode(): int
    {
       return self::CODE_ABORT;
    }

    public function getName(): string
    {
        return 'ABORT';
    }

    public function getParts(): array
    {
        return [$this->getDetails(), $this->getResponseUri()];
    }

    public function getResponseUri(): string
    {
        return $this->responseUri;
    }

    public function setResponseUri(string $responseUri): AbortMessage
    {
        $this->responseUri = $responseUri;
        return $this;
    }
}