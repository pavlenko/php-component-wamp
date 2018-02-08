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
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var bool
     */
    private $secure;

    /**
     * @param string $host
     * @param int    $port
     * @param bool   $secure
     */
    public function __construct($host = '127.0.0.1', $port = 8080, $secure = false)
    {
        $this->host   = $host;
        $this->port   = $port;
        $this->secure = $secure;
    }

    /**
     * @inheritDoc
     */
    public function start(Client $client, LoopInterface $loop)
    {
        $url = ($this->secure ? 'wss' : 'ws') . '://' . $this->host . ':' . $this->port;

        \Ratchet\Client\connect($url, ['wamp.2.json'], [], $loop)->then(
            function (WebSocket $socket) use ($client) {
                $connection = new WebSocketConnection($socket);
                $connection->setSerializer(new Serializer());

                $client->onOpen($connection);

                $socket->on('message', function ($message) use ($client, $connection) {
                    $client->onMessageReceived($connection->getSerializer()->deserialize($message));
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
                $client->onError($exception);
            }
        );
    }
}