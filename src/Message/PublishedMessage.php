<?php

namespace PE\Component\WAMP\Message;

use PE\Component\WAMP\MessageCode;

/**
 * Acknowledge sent by a Broker to a Publisher for acknowledged publications.
 *
 * <code>[PUBLISHED, PUBLISH.Request|id, Publication|id]</code>
 */
class PublishedMessage extends Message
{
    use RequestID;

    /**
     * @var int
     */
    private $publicationID;

    /**
     * @param int $requestID
     * @param int $publicationID
     */
    public function __construct($requestID, $publicationID)
    {
        $this->setRequestID($requestID);
        $this->setPublicationID($publicationID);
    }

    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return MessageCode::_PUBLISHED;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'PUBLISHED';
    }

    /**
     * @inheritDoc
     */
    public function getParts()
    {
        return [$this->getRequestID(), $this->getPublicationID()];
    }

    /**
     * @return int
     */
    public function getPublicationID()
    {
        return $this->publicationID;
    }

    /**
     * @param int $publicationID
     *
     * @return self
     */
    public function setPublicationID($publicationID)
    {
        $this->publicationID = (int) $publicationID;
        return $this;
    }
}