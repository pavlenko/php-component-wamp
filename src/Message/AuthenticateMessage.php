<?php

namespace PE\Component\WAMP\Message;

/**
 * The AUTHENTICATE message is used with certain Authentication Methods.
 * A Client having received a challenge is expected to respond by sending a signature or token.
 *
 * <code>[AUTHENTICATE, Signature|string, Extra|dict]</code>
 *
 * @codeCoverageIgnore
 */
final class AuthenticateMessage extends Message
{
    /**
     * @var string
     */
    private string $signature;

    /**
     * @var array
     */
    private array $extra;

    /**
     * @param string $signature
     * @param array  $extra
     */
    public function __construct(string $signature, array $extra)
    {
        $this->setSignature($signature);
        $this->setExtra($extra);
    }

    /**
     * @inheritDoc
     */
    public function getCode(): int
    {
        return self::CODE_AUTHENTICATE;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'AUTHENTICATE';
    }

    /**
     * @inheritDoc
     */
    public function getParts(): array
    {
        return [$this->getSignature(), $this->getExtra()];
    }

    /**
     * @return string
     */
    public function getSignature(): string
    {
        return $this->signature;
    }

    /**
     * @param string $signature
     *
     * @return self
     */
    public function setSignature(string $signature): AuthenticateMessage
    {
        $this->signature = (string) $signature;
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
    public function setExtra(array $extra): AuthenticateMessage
    {
        $this->extra = $extra;
        return $this;
    }
}