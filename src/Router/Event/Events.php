<?php

namespace PE\Component\WAMP\Router\Event;

final class Events
{
    /**
     * @Event("\PE\Component\WAMP\Router\Event\ConnectionEvent")
     */
    const CONNECTION_OPEN  = 'wamp.router.connection_open';

    /**
     * @Event("\PE\Component\WAMP\Router\Event\ConnectionEvent")
     */
    const CONNECTION_CLOSE = 'wamp.router.connection_close';

    /**
     * @Event("\PE\Component\WAMP\Router\Event\ConnectionEvent")
     */
    const CONNECTION_ERROR = 'wamp.router.connection_error';

    /**
     * @Event("\PE\Component\WAMP\Router\Event\MessageEvent")
     */
    const MESSAGE_RECEIVED = 'wamp.router.message_received';

    /**
     * @Event("\PE\Component\WAMP\Router\Event\MessageEvent")
     */
    const MESSAGE_SEND = 'wamp.router.message_send';
}