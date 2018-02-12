<?php

namespace PE\Component\WAMP\Router\Transport;

use PE\Component\WAMP\Router\Router;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ratchet\ConnectionInterface as RatchetConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\Http\HttpServerInterface;
use Ratchet\Server\IoServer;
use React\EventLoop\LoopInterface;
use React\Http\Response;
use React\Promise\Promise;
use React\Socket\Server;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollectionBuilder;

class LongPollTransport implements TransportInterface, HttpServerInterface
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
     * @var IoServer
     */
    private $server;

    /**
     * @inheritDoc
     */
    public function start(Router $router, LoopInterface $loop)
    {
        //TODO maybe use this as router instead of builtin ratchet
        $routes = new RouteCollectionBuilder();
        $routes->add('/open', $this);
        $routes->add('/{transport}/receive', $this);
        $routes->add('/{transport}/send', $this);
        $routes->add('/{transport}/close', $this);

        $socket = new Server('tcp://' . $this->host . ':' . $this->port, $loop);

        $this->server = new IoServer(
            new HttpServer(
                new \Ratchet\Http\Router(
                    new UrlMatcher($routes->build(), new RequestContext())
                )
            ),
            $socket,
            $loop
        );

        $server = new \React\Http\Server(function (ServerRequestInterface $request) use ($loop) {
            //TODO create async http server & add routing handler
            return new Promise(function ($resolve, $reject) use ($loop) {
                $loop->addTimer(1.5, function() use ($resolve) {
                    $response = new Response(
                        200,
                        array(
                            'Content-Type' => 'text/plain'
                        ),
                        'Hello world'
                    );
                    $resolve($response);
                });
            });
        });
    }

    /**
     * @inheritDoc
     */
    public function stop()
    {
        if ($this->server) {
            $this->server->socket->close();
        }
    }

    /**
     * @inheritDoc
     */
    public function onClose(RatchetConnectionInterface $ratchetConnection)
    {
        // TODO: Implement onClose() method.
    }

    /**
     * @inheritDoc
     */
    public function onError(RatchetConnectionInterface $ratchetConnection, \Exception $e)
    {
        // TODO: Implement onError() method.
    }

    /**
     * @inheritDoc
     */
    public function onOpen(RatchetConnectionInterface $ratchetConnection, RequestInterface $request = null)
    {
        // TODO: Implement onOpen() method.
    }

    /**
     * @inheritDoc
     */
    public function onMessage(RatchetConnectionInterface $ratchetConnection, $message)
    {
        // TODO: Implement onMessage() method.
    }
}