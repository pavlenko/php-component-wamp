<?php

namespace PE\Component\WAMP\Client\Transport;

use React\EventLoop\LoopInterface;
use React\HttpClient\Client;
use React\HttpClient\Response;
use React\Promise\Promise;
use React\Socket\Connector;

class LongPollClient
{
    /**
     * @var Client
     */
    private $http;

    /**
     * @var string
     */
    private $baseURI;

    /**
     * @var array
     */
    private $headers;

    /**
     * @param LoopInterface $loop
     * @param array         $options
     */
    public function __construct(LoopInterface $loop, array $options = [])
    {
        $this->baseURI = isset($options['base_uri'])
            ? $options['base_uri']
            : '';

        $this->headers = isset($options['headers']) && is_array($options['headers'])
            ? $options['headers']
            : [];

        $this->http = new Client($loop, new Connector($loop, $options));
    }

    /**
     * @param string      $method
     * @param string      $uri
     * @param string|null $data
     *
     * @return Promise
     */
    public function request($method, $uri, $data = null)
    {
        return new Promise(function ($resolve, $reject) use ($method, $uri, $data) {
            $request = $this->http->request(
                $method,
                rtrim($this->baseURI, '/') . '/' . ltrim($uri, '/'),
                $this->headers
            );

            $request->on('response', function (Response $response) use ($resolve, $reject) {
                $buffer = '';

                $response->on('data', function ($chunk) use (&$buffer) {
                    $buffer .= $chunk;
                });

                $response->on('end', function() use ($resolve, &$buffer) {
                    $resolve($buffer);
                });

                $response->on('error', function (\Exception $exception) use ($reject) {
                    $reject($exception);
                });
            });

            $request->on('error', function (\Exception $exception) use ($reject) {
                $reject($exception);
            });

            $request->end($data);
        });
    }
}