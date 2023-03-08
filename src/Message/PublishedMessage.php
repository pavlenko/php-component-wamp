<?php

namespace PE\Component\WAMP\Message;

/**
 * Acknowledge sent by a Broker to a Publisher for acknowledged publications.
 *
 * <code>[PUBLISHED, PUBLISH.Request|id, Publication|id]</code>
 *
 * @codeCoverageIgnore
 */
final class PublishedMessage extends Message
{
    use RequestID;

    /**
     * @var int
     */
    private int $publicationID;

    /**
     * @param int $requestID
     * @param int $publicationID
     */
    public function __construct(int $requestID, int $publicationID)
    {
        $this->setRequestID($requestID);
        $this->setPublicationID($publicationID);
    }

    /**
     * @inheritDoc
     */
    public function getCode(): int
    {
        return self::CODE_PUBLISHED;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'PUBLISHED';
    }

    /**
     * @inheritDoc
     */
    public function getParts(): array
    {
        return [$this->getRequestID(), $this->getPublicationID()];
    }

    /**
     * @return int
     */
    public function getPublicationID(): int
    {
        return $this->publicationID;
    }

    /**
     * @param int $publicationID
     *
     * @return self
     */
    public function setPublicationID(int $publicationID): PublishedMessage
    {
        $this->publicationID = (int) $publicationID;
        return $this;
    }
}
