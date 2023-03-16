<?php

namespace PE\Component\WAMP\Message;

/**
 * The CHALLENGE message is used with certain Authentication Methods.
 * During authenticated session establishment, a Router sends a challenge message.
 *
 * <code>[CHALLENGE, AuthMethod|string, Extra|dict]</code>
 *
 * @codeCoverageIgnore
 */
final class ChallengeMessage extends Message
{
    /**
     * @var string
     */
    private string $authenticationMethod;

    /**
     * @var array
     */
    private array $extra = [];

    /**
     * @param string $authenticationMethod
     * @param array  $extra
     */
    public function __construct(string $authenticationMethod, array $extra)
    {
        $this->setAuthenticationMethod($authenticationMethod);
        $this->setExtra($extra);
    }

    /**
     * @inheritDoc
     */
    public function getCode(): int
    {
        return self::CODE_CHALLENGE;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'CHALLENGE';
    }

    /**
     * @inheritDoc
     */
    public function getParts(): array
    {
        return [$this->getAuthenticationMethod(), $this->getExtra()];
    }

    /**
     * @return string
     */
    public function getAuthenticationMethod(): string
    {
        return $this->authenticationMethod;
    }

    /**
     * @param string $authenticationMethod
     *
     * @return self
     */
    public function setAuthenticationMethod(string $authenticationMethod): ChallengeMessage
    {
        $this->authenticationMethod = $authenticationMethod;
        return $this;
    }

    /**
     * @return array
     */
    public function getExtra(): array
    {
        return $this->extra;
    }

    /**
     * @param array $extra
     *
     * @return self
     */
    public function setExtra(array $extra): ChallengeMessage
    {
        $this->extra = $extra;
        return $this;
    }
}
