<?php

namespace PE\Component\WAMP\Client\Transport;

use PE\Component\WAMP\Connection\Connection;
use PE\Component\WAMP\Message\Message;

class LongPollConnection extends Connection
{
    /**
     * @var LongPollClient
     */
    private $client;

    public function __construct(LongPollClient $client)
    {
        $this->client  = $client;
    }

    /**
     * @inheritDoc
     */
    public function send(Message $message)
    {
        $this->client->request('POST', '/send', $this->getSerializer()->serialize($message));
    }

    /**
     * @inheritDoc
     */
    public function close()
    {
        $this->client->request('POST', '/close');
    }

    /**
     * @inheritDoc
     */
    public function ping()
    {
        // TODO: Implement ping() method.
    }

    /**
     * @inheritDoc
     */
    public function getTransportDetails()
    {
        // TODO: Implement getTransportDetails() method.
    }
}