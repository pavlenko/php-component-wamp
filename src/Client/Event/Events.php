<?php

namespace PE\Component\WAMP\Client\Event;

final class Events
{
    /**
     * @Event("\PE\Component\WAMP\Client\Event\ConnectionEvent")
     */
    const CONNECTION_OPEN  = 'wamp.client.connection_open';

    /**
     * @Event("\PE\Component\WAMP\Client\Event\ConnectionEvent")
     */
    const CONNECTION_CLOSE = 'wamp.client.connection_close';

    /**
     * @Event("\PE\Component\WAMP\Client\Event\ConnectionEvent")
     */
    const CONNECTION_ERROR = 'wamp.client.connection_error';

    /**
     * @Event("\PE\Component\WAMP\Client\Event\ConnectionEvent")
     */
    const SESSION_ESTABLISHED = 'wamp.client.session_established';

    /**
     * @Event("\PE\Component\WAMP\Client\Event\MessageEvent")
     */
    const MESSAGE_RECEIVED = 'wamp.client.message_received';

    /**
     * @Event("\PE\Component\WAMP\Client\Event\MessageEvent")
     */
    const MESSAGE_SEND = 'wamp.client.message_send';
}