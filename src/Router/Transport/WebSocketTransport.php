<?php

namespace PE\Component\WAMP\Router\Transport;

use Ratchet\ConnectionInterface as RatchetConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\MessageComponentInterface;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\LoopInterface;
use React\Socket\Server;
use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\Router\Router;
use PE\Component\WAMP\Serializer\Serializer;

class WebSocketTransport implements TransportInterface, MessageComponentInterface
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
     * @var \SplObjectStorage|ConnectionInterface[]
     */
    private $connections;

    /**
     * @var IoServer
     */
    private $server;

    /**
     * @var Router
     */
    private $router;

    /**
     * @param string $host
     * @param int    $port
     */
    public function __construct($host = '127.0.0.1', $port = 8080)
    {
        $this->host = $host;
        $this->port = $port;

        $this->connections = new \SplObjectStorage();
    }

    /**
     * @inheritDoc
     */
    public function start(Router $router, LoopInterface $loop)
    {
        $this->router = $router;

        $socket = new Server('tcp://' . $this->host . ':' . $this->port, $loop);

        $this->server = new IoServer(
            new HttpServer(
                new WsServer($this)
            ),
            $socket,
            $loop
        );
    }

    /**
     * @inheritDoc
     */
    public function stop()
    {
        if ($this->server) {
            $this->server->socket->close();
        }

        foreach ($this->connections as $k) {//TODO stop connection?
            $this->connections[$k]->shutdown();
        }
    }

    /**
     * @inheritDoc
     */
    public function onOpen(RatchetConnectionInterface $ratchetConnection)
    {
        $connection = new WebSocketConnection($ratchetConnection);
        $connection->setSerializer(new Serializer());

        $this->connections->attach($ratchetConnection, $connection);

        $this->router->onOpen($connection);
    }

    /**
     * @inheritDoc
     */
    public function onClose(RatchetConnectionInterface $ratchetConnection)
    {
        $connection = $this->connections[$ratchetConnection];

        $this->connections->detach($ratchetConnection);

        unset($this->connections[$ratchetConnection]);

        $this->router->onClose($connection);
    }

    /**
     * @inheritDoc
     */
    public function onError(RatchetConnectionInterface $ratchetConnection, \Exception $exception)
    {
        $connection = $this->connections[$ratchetConnection];

        $this->router->onError($connection, $exception);
    }

    /**
     * @inheritDoc
     */
    public function onMessage(RatchetConnectionInterface $ratchetConnection, MessageInterface $message)
    {
        $connection = $this->connections[$ratchetConnection];

        try {
            $deserialized = $connection->getSerializer()->deserialize($message);

            $this->router->onMessage($connection, $deserialized);
        } catch (\Exception $exception) {}
    }
}