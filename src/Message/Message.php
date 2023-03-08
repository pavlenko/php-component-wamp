<?php

namespace PE\Component\WAMP\Message;

abstract class Message implements \JsonSerializable
{
    public const CODE_UNKNOWN      = 0;
    public const CODE_HELLO        = 1;
    public const CODE_WELCOME      = 2;
    public const CODE_ABORT        = 3;
    public const CODE_CHALLENGE    = 4; // advanced
    public const CODE_AUTHENTICATE = 5; // advanced
    public const CODE_GOODBYE      = 6;
    public const CODE_HEARTBEAT    = 7; // advanced
    public const CODE_ERROR        = 8;
    public const CODE_PUBLISH      = 16;
    public const CODE_PUBLISHED    = 17;
    public const CODE_SUBSCRIBE    = 32;
    public const CODE_SUBSCRIBED   = 33;
    public const CODE_UNSUBSCRIBE  = 34;
    public const CODE_UNSUBSCRIBED = 35;
    public const CODE_EVENT        = 36;
    public const CODE_CALL         = 48;
    public const CODE_CANCEL       = 49; // advanced
    public const CODE_RESULT       = 50;
    public const CODE_REGISTER     = 64;
    public const CODE_REGISTERED   = 65;
    public const CODE_UNREGISTER   = 66;
    public const CODE_UNREGISTERED = 67;
    public const CODE_INVOCATION   = 68;
    public const CODE_INTERRUPT    = 69; // advanced
    public const CODE_YIELD        = 70;

    public const NAMES = [
        self::CODE_UNKNOWN      => 'UNKNOWN',
        self::CODE_HELLO        => 'HELLO',
        self::CODE_WELCOME      => 'WELCOME',
        self::CODE_ABORT        => 'ABORT',
        self::CODE_CHALLENGE    => 'CHALLENGE',
        self::CODE_AUTHENTICATE => 'AUTHENTICATE',
        self::CODE_GOODBYE      => 'GOODBYE',
        self::CODE_HEARTBEAT    => 'HEARTBEAT',
        self::CODE_ERROR        => 'ERROR',
        self::CODE_PUBLISH      => 'PUBLISH',
        self::CODE_PUBLISHED    => 'PUBLISHED',
        self::CODE_SUBSCRIBE    => 'SUBSCRIBE',
        self::CODE_SUBSCRIBED   => 'SUBSCRIBED',
        self::CODE_UNSUBSCRIBE  => 'UNSUBSCRIBE',
        self::CODE_UNSUBSCRIBED => 'UNSUBSCRIBED',
        self::CODE_EVENT        => 'EVENT',
        self::CODE_CALL         => 'CALL',
        self::CODE_CANCEL       => 'CANCEL',
        self::CODE_RESULT       => 'RESULT',
        self::CODE_REGISTER     => 'REGISTER',
        self::CODE_REGISTERED   => 'REGISTERED',
        self::CODE_UNREGISTER   => 'UNREGISTER',
        self::CODE_UNREGISTERED => 'UNREGISTERED',
        self::CODE_INVOCATION   => 'INVOCATION',
        self::CODE_INTERRUPT    => 'INTERRUPT',
        self::CODE_YIELD        => 'YIELD',
    ];

    /**
     * @return int
     */
    abstract public function getCode(): int;

    /**
     * @return string
     */
    abstract public function getName(): string;

    /**
     * @return array
     */
    abstract public function getParts(): array;

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return array_merge([$this->getCode()], $this->getParts());
    }
}
