<?php

namespace PE\Component\WAMP\Message;

/**
 * <code>[WELCOME, Session|id, Details|dict]</code>
 *
 * @codeCoverageIgnore
 */
final class WelcomeMessage extends Message
{
    use Details;

    /**
     * @var int
     */
    private int $sessionId;

    /**
     * @param int $sessionId
     * @param array $details
     */
    public function __construct(int $sessionId, array $details)
    {
        $this->setSessionId($sessionId);
        $this->setDetails($details);
    }

    /**
     * @inheritDoc
     */
    public function getCode(): int
    {
        return self::CODE_WELCOME;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'WELCOME';
    }

    /**
     * @inheritDoc
     */
    public function getParts(): array
    {
        return [$this->getSessionId(), $this->getDetails()];
    }

    /**
     * @return int
     */
    public function getSessionId(): int
    {
        return $this->sessionId;
    }

    /**
     * @param int $sessionId
     *
     * @return self
     */
    public function setSessionId(int $sessionId): WelcomeMessage
    {
        $this->sessionId = $sessionId;
        return $this;
    }
}
