<?php

namespace PE\Component\WAMP\Client\Transport;

use Ratchet\Client\WebSocket;
use React\EventLoop\LoopInterface;
use PE\Component\WAMP\Client\Client;
use PE\Component\WAMP\Serializer\Serializer;

class WebSocketTransport implements TransportInterface
{
    /**
     * @var string
     */
    private $URL;

    /**
     * @param string $URL
     */
    public function __construct($URL = 'ws://127.0.0.1:8080/')
    {
        $this->URL = $URL;
    }

    /**
     * @inheritDoc
     */
    public function start(Client $client, LoopInterface $loop)
    {
        \Ratchet\Client\connect($this->URL, ['wamp.2.json'], [], $loop)->then(
            function (WebSocket $socket) use ($client) {
                $connection = new WebSocketConnection($socket);
                $connection->setSerializer(new Serializer());

                $client->onOpen($connection);

                $socket->on('message', function ($message) use ($client, $connection) {
                    $client->onMessage($connection->getSerializer()->deserialize($message));
                });

                $socket->on('close', function ($code, $reason) use ($client) {
                    $client->onClose($reason);
                });

                $socket->on('error', function ($error) use ($client) {
                    $client->onError($error);
                });
            },
            function (\Exception $exception) use ($client) {
                $client->onClose('unreachable');
            }
        );
    }
}