<?php

namespace PE\Component\WAMP\Client\Transport;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Ratchet\Client\Connector;
use Ratchet\Client\WebSocket;
use React\EventLoop\LoopInterface;
use PE\Component\WAMP\Client\Client;
use PE\Component\WAMP\Serializer\Serializer;

class WebSocketTransport implements TransportInterface,  LoggerAwareInterface
{
    use LoggerAwareTrait;

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
     * @var int
     */
    private $timeout;

    /**
     * @param string $host
     * @param int    $port
     * @param bool   $secure
     * @param int    $timeout
     */
    public function __construct($host = '127.0.0.1', $port = 8080, $secure = false, $timeout = 20)
    {
        $this->host    = $host;
        $this->port    = $port;
        $this->secure  = $secure;
        $this->timeout = $timeout;
    }

    /**
     * @inheritDoc
     */
    public function start(Client $client, LoopInterface $loop)
    {
        $url = ($this->secure ? 'wss' : 'ws') . '://' . $this->host . ':' . $this->port;

        !$this->logger ?: $this->logger->info('Connecting to {url} ...', ['url' => $url]);

        $connector = new Connector(
            $loop,
            new \React\Socket\Connector($loop, ['timeout' => $this->timeout])
        );

        $promise = $connector($url, ['wamp.2.json'], []);
        $promise->then(
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