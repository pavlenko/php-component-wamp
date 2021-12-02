<?php

namespace PE\Component\WAMP;

final class MessageCode
{
    const _UNKNOWN      = 0;
    const _HELLO        = 1;
    const _WELCOME      = 2;
    const _ABORT        = 3;
    const _CHALLENGE    = 4; // advanced
    const _AUTHENTICATE = 5; // advanced
    const _GOODBYE      = 6;
    const _HEARTBEAT    = 7; // advanced
    const _ERROR        = 8;
    const _PUBLISH      = 16;
    const _PUBLISHED    = 17;
    const _SUBSCRIBE    = 32;
    const _SUBSCRIBED   = 33;
    const _UNSUBSCRIBE  = 34;
    const _UNSUBSCRIBED = 35;
    const _EVENT        = 36;
    const _CALL         = 48;
    const _CANCEL       = 49; // advanced
    const _RESULT       = 50;
    const _REGISTER     = 64;
    const _REGISTERED   = 65;
    const _UNREGISTER   = 66;
    const _UNREGISTERED = 67;
    const _INVOCATION   = 68;
    const _INTERRUPT    = 69; // advanced
    const _YIELD        = 70;
    
    private function __construct()
    {}
}
