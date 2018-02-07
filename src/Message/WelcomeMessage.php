<?php

namespace PE\Component\WAMP\Message;

use PE\Component\WAMP\MessageCode;

/**
 * <code>[WELCOME, Session|id, Details|dict]</code>
 */
class WelcomeMessage extends Message
{
    use Details;

    /**
     * @var int
     */
    private $sessionId;

    /**
     * @param int   $sessionId
     * @param array $details
     */
    public function __construct($sessionId, array $details)
    {
        $this->setSessionId($sessionId);
        $this->setDetails($details);
    }

    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return MessageCode::_WELCOME;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'WELCOME';
    }

    /**
     * @inheritDoc
     */
    public function getParts()
    {
        return [$this->getSessionId(), $this->getDetails()];
    }

    /**
     * @return int
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @param int $sessionId
     *
     * @return self
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = (int) $sessionId;
        return $this;
    }
}