<?php

namespace PE\Component\WAMP\Message;

/**
 * Sent by a Client to initiate opening of a WAMP session to a Router attaching to a Realm.
 *
 * <code>[HELLO, Realm|uri, Details|dict]</code>
 */
final class HelloMessage extends Message
{
    use Details;

    /**
     * @var string
     */
    private string $realm;

    /**
     * @param string $realm
     * @param array  $details
     */
    public function __construct(string $realm, array $details)
    {
        $this->setRealm($realm);
        $this->setDetails($details);
    }

    /**
     * @inheritDoc
     */
    public function getCode(): int
    {
        return self::CODE_HELLO;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'HELLO';
    }

    /**
     * @inheritDoc
     */
    public function getParts(): array
    {
        return [$this->getRealm(), $this->getDetails()];
    }

    /**
     * @return string
     */
    public function getRealm(): string
    {
        return $this->realm;
    }

    /**
     * @param string $realm
     *
     * @return self
     */
    public function setRealm($realm): HelloMessage
    {
        $this->realm = (string) $realm;
        return $this;
    }
}
