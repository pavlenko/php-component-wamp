<?php

namespace PE\Component\WAMP\Message;

use PE\Component\WAMP\MessageCode;

/**
 * The CHALLENGE message is used with certain Authentication Methods.
 * During authenticated session establishment, a Router sends a challenge message.
 *
 * <code>[CHALLENGE, AuthMethod|string, Extra|dict]</code>
 */
class ChallengeMessage extends Message
{
    /**
     * @var string
     */
    private $authenticationMethod;

    /**
     * @var array
     */
    private $extra = [];

    /**
     * @param string $authMethod
     * @param array  $extra
     */
    public function __construct($authMethod, array $extra)
    {
        $this->setAuthenticationMethod($authMethod);
        $this->setExtra($extra);
    }

    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return MessageCode::_CHALLENGE;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'CHALLENGE';
    }

    /**
     * @inheritDoc
     */
    public function getParts()
    {
        return [$this->getAuthenticationMethod(), $this->getExtra()];
    }

    /**
     * @return string
     */
    public function getAuthenticationMethod()
    {
        return $this->authenticationMethod;
    }

    /**
     * @param string $authenticationMethod
     *
     * @return self
     */
    public function setAuthenticationMethod($authenticationMethod)
    {
        $this->authenticationMethod = (string) $authenticationMethod;
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