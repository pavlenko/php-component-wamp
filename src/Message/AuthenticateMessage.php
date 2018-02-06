<?php

namespace PE\Component\WAMP\Message;

use PE\Component\WAMP\MessageCode;

/**
 * The AUTHENTICATE message is used with certain Authentication Methods.
 * A Client having received a challenge is expected to respond by sending a signature or token.
 *
 * <code>[AUTHENTICATE, Signature|string, Extra|dict]</code>
 */
class AuthenticateMessage extends Message
{
    /**
     * @var string
     */
    private $signature;

    /**
     * @var array
     */
    private $extra;

    /**
     * @param string $signature
     * @param array  $extra
     */
    public function __construct($signature, array $extra)
    {
        $this->setSignature($signature);
        $this->setExtra($extra);
    }

    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return MessageCode::_AUTHENTICATE;
    }

    /**
     * @inheritDoc
     */
    public function getParts()
    {
        return [$this->getSignature(), $this->getExtra()];
    }

    /**
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @param string $signature
     *
     * @return self
     */
    public function setSignature($signature)
    {
        $this->signature = (string) $signature;
        return $this;
    }

    /**
     * @return array
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * @param array $extra
     *
     * @return self
     */
    public function setExtra(array $extra)
    {
        $this->extra = $extra;
        return $this;
    }
}