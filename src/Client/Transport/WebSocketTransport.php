<?php

namespace PE\Component\WAMP\Client\Transport;

use PE\Component\WAMP\Client\Client;
use Psr\Log\LoggerInterface;
use Ratchet\Client\Connector;
use Ratchet\Client\WebSocket;
use React\EventLoop\LoopInterface;
use PE\Component\WAMP\Serializer\Serializer;

final class WebSocketTransport implements TransportInterface
{
    private string $host;

    private int $port;

    private bool $secure;

    private int $timeout;

    public function __construct(string $host = '127.0.0.1', int $port = 8080, bool $secure = true, int $timeout = 20)
    {
        $this->host    = $host;
        $this->port    = $port;
        $this->secure  = $secure;
        $this->timeout = $timeout;
    }

    public function start(Client $client, LoopInterface $loop, LoggerInterface $logger): void
    {
        $url = ($this->secure ? 'wss' : 'ws') . '://' . $this->host . ':' . $this->port;

        $logger->info('Web-socket: connecting to {url} ...', ['url' => $url]);

        $connector = new Connector(
            $loop,
            new \React\Socket\Connector($loop, ['timeout' => $this->timeout])
        );

        $promise = $connector($url, ['wamp.2.json'], []);
        $promise
            ->then(function (WebSocket $socket) use ($client) {
                $connection = new WebSocketConnection($socket);
                $connection->setSerializer(new Serializer());

                $client->processOpen($connection);

                $socket->on('message', function ($message) use ($client, $connection) {
                    $client->processMessageReceived($connection->getSerializer()->deserialize($message));
                });

                $socket->on('close', function ($code, $reason) use ($client) {
                    $client->processClose($reason);
                });

                $socket->on('error', function ($error) use ($client) {
                    $client->processError($error);
                });
            })
            ->otherwise(function(\Throwable $throwable) use ($client) {
                $client->processClose('unreachable');
                $client->processError($throwable);
            });
    }
}
