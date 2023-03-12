<?php

namespace PE\Component\WAMP\Client;

use PE\Component\WAMP\Message\Message;

interface ClientInterface
{
    public const EVENT_CONNECTION_OPEN     = 'wamp.client.connection_open';
    public const EVENT_CONNECTION_CLOSE    = 'wamp.client.connection_close';
    public const EVENT_CONNECTION_ERROR    = 'wamp.client.connection_error';
    public const EVENT_SESSION_ESTABLISHED = 'wamp.client.session_established';
    public const EVENT_MESSAGE_RECEIVED    = 'wamp.client.message_received';
    public const EVENT_MESSAGE_SEND        = 'wamp.client.message_send';

    public function processMessageSend(Message $message): void;
}