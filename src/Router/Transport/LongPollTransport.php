<?php

namespace PE\Component\WAMP\Router\Transport;

use PE\Component\WAMP\Router\Router;
use PE\Component\WAMP\Util;
use Psr\Http\Message\ServerRequestInterface;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use React\EventLoop\LoopInterface;
use React\Http\Response;
use React\Promise\Deferred;
use React\Promise\Promise;
use React\Socket\Server;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollectionBuilder;

class LongPollTransport implements TransportInterface
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
     * @var \SplObjectStorage|LongPollConnection[]
     */
    private $connections;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var IoServer
     */
    private $server;

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
        //TODO maybe use this as router instead of builtin ratchet
        $routes = new RouteCollectionBuilder();
        $routes->add('/open', $this);
        $routes->add('/{transportID}/receive', $this);
        $routes->add('/{transportID}/send', $this);
        $routes->add('/{transportID}/close', $this);

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

        $this->router = $router;

        $matcher = new UrlMatcher($routes->build(), new RequestContext());

        $socket = new Server('tcp://' . $this->host . ':' . $this->port, $loop);
        $server = new \React\Http\Server(function (ServerRequestInterface $request) use ($matcher) {
            $uri = $request->getUri();

            $context = $matcher->getContext();
            $context->setMethod($request->getMethod());
            $context->setHost($uri->getHost());

            try {
                $route = $matcher->match($uri->getHost());
            } catch (MethodNotAllowedException $exception) {
                return new Response(405, ['Allow' => $exception->getAllowedMethods()]);
            } catch (ResourceNotFoundException $exception) {
                return new Response(404);
            }

            switch ($route['_route']) {
                case 'open':
                    return $this->processOpen();
                    break;
                case 'receive':
                    //TODO Create promise which resolved when called connection->send()
                    return $this->processReceive($route['transportID']);
                    break;
                case 'send':
                    return $this->processIncomingMessage($route['transportID'], (string) $request->getBody());
                    break;
                case 'close':
                    //TODO Destroy connection instance
                    return $this->processClose($route['transportID']);
                    break;
            }

            return new Response(500);
        });

        $server->listen($socket);
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
     * @return Response
     */
    private function processOpen()
    {
        $transportID = (string) Util::generateID();

        $this->connections[$transportID] = new LongPollConnection();

        return new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode(['transport' => $transportID, 'protocol'  => 'wamp.2.json'])
        );
    }

    /**
     * @param string $transportID
     *
     * @return Promise
     */
    private function processReceive($transportID)
    {
        $deferred = new Deferred();
        $deferred->promise()->then(function ($message) {
            return new Response(200, [], $message);
        });

        $connection = $this->connections[$transportID];
        $connection->setDeferred($deferred);

        return $deferred->promise();
    }

    /**
     * @param string $transportID
     * @param string $requestBody
     */
    private function processIncomingMessage($transportID, $requestBody)
    {
        $connection = $this->connections[$transportID];

        $this->router->onMessage($connection, $connection->getSerializer()->deserialize($requestBody));
    }

    /**
     * @param string $transportID
     *
     * @return Response
     */
    private function processClose($transportID)
    {
        $connection = $this->connections[$transportID];
        $connection->close();

        unset($this->connections[$transportID]);

        return new Response(202);
    }
}