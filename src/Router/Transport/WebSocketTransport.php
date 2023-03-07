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
use React\Socket\Server;
use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\Router\Router;
use PE\Component\WAMP\Serializer\Serializer;

final class WebSocketTransport implements TransportInterface, MessageComponentInterface, WsServerInterface
{
    /**
     * @var string
     */
    private string $host;

    /**
     * @var int
     */
    private int $port;

    /**
     * @var bool
     */
    private bool $secure;

    /**
     * @var \SplObjectStorage|ConnectionInterface[]
     */
    private $connections;

    /**
     * @var IoServer
     */
    private IoServer $server;

    /**
     * @var Router
     */
    private Router $router;

    /**
     * @var \SessionHandlerInterface|null
     */
    private ?\SessionHandlerInterface $sessionHandler;

    /**
     * @param string $host
     * @param int $port
     * @param bool $secure
     */
    public function __construct(string $host = '127.0.0.1', int $port = 8080, bool $secure = false)
    {
        $this->host   = $host;
        $this->port   = $port;
        $this->secure = $secure;

        $this->connections = new \SplObjectStorage();
    }

    /**
     * @param \SessionHandlerInterface|null $sessionHandler
     */
    public function setSessionHandler(\SessionHandlerInterface $sessionHandler = null): void
    {
        $this->sessionHandler = $sessionHandler;
    }

    /**
     * @inheritDoc
     */
    public function getSubProtocols(): array
    {
        return ['wamp.2.json'];
    }

    /**
     * @inheritDoc
     */
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
            new Server($uri, $loop),
            $loop
        );
    }

    /**
     * @inheritDoc
     */
    public function stop(): void
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
    public function onOpen(RatchetConnectionInterface $ratchetConnection): void
    {
        $connection = new WebSocketConnection($ratchetConnection);
        $connection->setSerializer(new Serializer());

        $this->connections->attach($ratchetConnection, $connection);

        $this->router->processOpen($connection);
    }

    /**
     * @inheritDoc
     */
    public function onClose(RatchetConnectionInterface $ratchetConnection): void
    {
        $connection = $this->connections[$ratchetConnection];

        $this->connections->detach($ratchetConnection);

        unset($this->connections[$ratchetConnection]);

        $this->router->processClose($connection);
    }

    /**
     * @inheritDoc
     */
    public function onError(RatchetConnectionInterface $ratchetConnection, \Exception $exception): void
    {
        $connection = $this->connections[$ratchetConnection];

        $this->router->processError($connection, $exception);
    }

    /**
     * @inheritDoc
     */
    public function onMessage(RatchetConnectionInterface $ratchetConnection, MessageInterface $message): void
    {
        $connection = $this->connections[$ratchetConnection];

        try {
            $deserialized = $connection->getSerializer()->deserialize($message);

            $this->router->processMessageReceived($connection, $deserialized);
        } catch (\Exception $exception) {}
    }
}
