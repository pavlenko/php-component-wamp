<?php

namespace PE\Component\WAMP\Router\Transport;

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

class WebSocketTransport implements TransportInterface, MessageComponentInterface, WsServerInterface
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
     * @var \SessionHandlerInterface|null
     */
    private $sessionHandler;

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

        $this->connections = new \SplObjectStorage();
    }

    /**
     * @param \SessionHandlerInterface|null $sessionHandler
     */
    public function setSessionHandler(\SessionHandlerInterface $sessionHandler = null)
    {
        $this->sessionHandler = $sessionHandler;
    }

    /**
     * @inheritDoc
     */
    public function getSubProtocols()
    {
        return ['wamp.2.json'];
    }

    /**
     * @inheritDoc
     */
    public function start(Router $router, LoopInterface $loop)
    {
        $uri = ($this->secure ? 'tls' : 'tcp') . '://' . $this->host . ':' . $this->port;

        $router->getLogger() && $router->getLogger()->info('Web-socket: listen to ' . $uri);

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

        $this->router->processOpen($connection);
    }

    /**
     * @inheritDoc
     */
    public function onClose(RatchetConnectionInterface $ratchetConnection)
    {
        $connection = $this->connections[$ratchetConnection];

        $this->connections->detach($ratchetConnection);

        unset($this->connections[$ratchetConnection]);

        $this->router->processClose($connection);
    }

    /**
     * @inheritDoc
     */
    public function onError(RatchetConnectionInterface $ratchetConnection, \Exception $exception)
    {
        $connection = $this->connections[$ratchetConnection];

        $this->router->processError($connection, $exception);
    }

    /**
     * @inheritDoc
     */
    public function onMessage(RatchetConnectionInterface $ratchetConnection, MessageInterface $message)
    {
        $connection = $this->connections[$ratchetConnection];

        try {
            $deserialized = $connection->getSerializer()->deserialize($message);

            $this->router->processMessageReceived($connection, $deserialized);
        } catch (\Exception $exception) {}
    }
}