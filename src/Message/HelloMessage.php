<?php

namespace PE\Component\WAMP\Message;

use PE\Component\WAMP\MessageCode;

/**
 * Sent by a Client to initiate opening of a WAMP session to a Router attaching to a Realm.
 *
 * <code>[HELLO, Realm|uri, Details|dict]</code>
 */
class HelloMessage extends Message
{
    use Details;

    /**
     * @var string
     */
    private $realm;

    /**
     * @param string $realm
     * @param array  $details
     */
    public function __construct($realm, array $details)
    {
        $this->setRealm($realm);
        $this->setDetails($details);
    }

    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return MessageCode::_HELLO;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'HELLO';
    }

    /**
     * @inheritDoc
     */
    public function getParts()
    {
        return [$this->getRealm(), $this->getDetails()];
    }

    /**
     * @return string
     */
    public function getRealm()
    {
        return $this->realm;
    }

    /**
     * @param string $realm
     *
     * @return self
     */
    public function setRealm($realm)
    {
        $this->realm = (string) $realm;
        return $this;
    }
}