<?php

namespace PE\Component\WAMP\Router\Transport;

use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface as RatchetConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\Server\IoServer;
use Ratchet\Session\SessionProvider;
use Ratchet\WebSocket\MessageComponentInterface;
use Ratchet\WebSocket\WsServer;
use Ratchet\WebSocket\WsServerInterface;
use React\EventLoop\LoopInterface;
use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\Router\Router;
use PE\Component\WAMP\Serializer\Serializer;
use React\Socket\SocketServer;

final class WebSocketTransport implements TransportInterface, MessageComponentInterface, WsServerInterface
{
    private string $host;
    private int $port;
    private bool $secure;

    /**
     * @var \SplObjectStorage|ConnectionInterface[]
     */
    private $connections;
    private ?IoServer $server = null;
    private Router $router;
    private ?\SessionHandlerInterface $sessionHandler = null;

    public function __construct(string $host = '127.0.0.1', int $port = 8080, bool $secure = false)
    {
        $this->host   = $host;
        $this->port   = $port;
        $this->secure = $secure;

        $this->connections = new \SplObjectStorage();
    }

    public function setSessionHandler(\SessionHandlerInterface $sessionHandler = null): void
    {
        $this->sessionHandler = $sessionHandler;
    }

    public function getSubProtocols(): array
    {
        return ['wamp.2.json'];
    }

    public function start(Router $router, LoopInterface $loop, LoggerInterface $logger): void
    {
        $uri = ($this->secure ? 'tls' : 'tcp') . '://' . $this->host . ':' . $this->port;

        $logger->info('Web-socket: listen to ' . $uri);

        $this->router = $router;

        $wsServer = $this->sessionHandler
            ? new SessionProvider(new WsServer($this), $this->sessionHandler)
            : new WsServer($this);

        $this->server = new IoServer(
            new HttpServer($wsServer),
            new SocketServer($uri, [], $loop),
            $loop
        );
    }

    public function stop(): void
    {
        if ($this->server) {
            $this->server->socket->close();
        }

        foreach ($this->connections as $k) {
            $this->connections[$k]->shutdown();
        }
    }

    public function onOpen(RatchetConnectionInterface $conn): void
    {
        $connection = new WebSocketConnection($conn);
        $connection->setSerializer(new Serializer());

        $this->connections->attach($conn, $connection);

        $this->router->processOpen($connection);
    }

    public function onClose(RatchetConnectionInterface $conn): void
    {
        $connection = $this->connections[$conn];

        $this->connections->detach($conn);

        unset($this->connections[$conn]);

        $this->router->processClose($connection);
    }

    public function onError(RatchetConnectionInterface $conn, \Exception $e): void
    {
        $connection = $this->connections[$conn];

        $this->router->processError($connection, $e);
    }

    public function onMessage(RatchetConnectionInterface $conn, MessageInterface $msg): void
    {
        $connection = $this->connections[$conn];

        try {
            $this->router->processMessageReceived($connection, $connection->getSerializer()->deserialize($msg));
        } catch (\Exception $exception) {}
    }
}
