<?php

namespace PE\Component\WAMP\Client\Transport;

use GuzzleHttp\ClientInterface;
use PE\Component\WAMP\Connection\Connection;
use PE\Component\WAMP\Message\Message;
use React\EventLoop\Timer\TimerInterface;

class LongPollConnection extends Connection
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var TimerInterface
     */
    private $timer;

    /**
     * @param ClientInterface $client
     * @param TimerInterface  $timer
     */
    public function __construct(ClientInterface $client, TimerInterface $timer)
    {
        $this->client = $client;
        $this->timer  = $timer;
    }

    /**
     * @inheritDoc
     */
    public function send(Message $message)
    {
        $this->client->request('POST', 'send', ['body' => $this->getSerializer()->serialize($message)]);
    }

    /**
     * @inheritDoc
     */
    public function close()
    {
        $this->timer->cancel();
        $this->client->request('POST', 'close');
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