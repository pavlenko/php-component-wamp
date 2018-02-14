<?php

namespace PE\Component\WAMP\Client\Transport;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\RequestException;
use PE\Component\WAMP\Client\Client;
use PE\Component\WAMP\Serializer\Serializer;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\HttpClient\Client as HttpClient;
use React\HttpClient\Response;
use React\Promise\Promise;
use React\Socket\Connector;

class LongPollTransport implements TransportInterface, LoggerAwareInterface
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
     * @var string
     */
    private $uri;

    /**
     * @param string $host
     * @param int    $port
     * @param bool   $secure
     */
    public function __construct($host = '127.0.0.1', $port = 8080, $secure = false)
    {
        $this->host    = $host;
        $this->port    = $port;
        $this->secure  = $secure;

        $this->uri = ($this->secure ? 'https' : 'http') . '://' . $this->host . ':' . $this->port;
    }

    /**
     * @inheritDoc
     */
    public function start(Client $client, LoopInterface $loop)
    {
        $this->logger && $this->logger->info('Connecting to {url} ...', ['url' => $this->uri]);

        $http = new LongPollClient($loop, [
            'base_uri' => $this->uri,
            'timeout'  => 5,
            'headers'  => ['Content-Type' => 'application/json']
        ]);

        $http->request('POST', '/open', '{"protocols": ["wamp.2.json"]}')->then(
            function ($response) use ($client, $loop) {
                $response = json_decode($response);

                if (!isset($response->protocol, $response->transport)) {
                    throw new \RuntimeException('Invalid response');
                }

                $http = new LongPollClient($loop, [
                    'base_uri' => rtrim($this->uri, '/') . '/' . $response->transport,
                    'timeout'  => false,
                    'headers'  => ['Content-Type' => 'application/json']
                ]);

                $connection = new LongPollConnection($http);
                $connection->setSerializer($serializer = new Serializer());

                $client->processOpen($connection);

                $http->request('POST', '/receive')->then(
                    function ($message) use ($client, $connection) {
                        $client->processMessageReceived($connection->getSerializer()->deserialize($message));
                    }
                );
            },
            function ($error) {
                $this->logger && $this->logger->error($error);
            }
        );

        return;
        $http = new HttpClient($loop, new Connector($loop, ['timeout' => 30]));

        $promise = $this->request($http, '/open', '{"protocols": ["wamp.2.json"]}');
        $promise->then(
            function ($response) use ($client, $http) {
                $this->logger && $this->logger->debug($response);
                if ($transport = json_decode($response)->transport) {
                    // Create connection
                    $connection = new LongPollConnection(
                        function ($message) use ($http, $transport) {
                            $this->request($http, $transport . '/send', $message);
                        },
                        function () use ($http, $transport) {
                            $this->request($http, $transport . '/close');
                        }
                    );

                    $connection->setSerializer($serializer = new Serializer());

                    $client->processOpen($connection);

                    $this->processReceive($http, $client, $serializer, $transport);
                }
            },
            function ($error) {
                echo $error . "\n";
            }
        );

        return;

        // Create HTTP client for open connection
        $http = new GuzzleHttpClient([
            'base_uri' => $url,
            'timeout'  => $this->timeout
        ]);

        $promise = $http->requestAsync('POST', '/open', ['body' => '{"protocols": ["wamp.2.json"]}']);
        $promise->then(
            function (ResponseInterface $response) use ($client, $loop, $url) {
                $json = json_decode((string) $response->getBody());

                if (isset($json->protocol, $json->transport)) {
                    $serializer = new Serializer();

                    // Create HTTP client with configured base url
                    $http = new GuzzleHttpClient([
                        'base_uri' => $url . '/' . $json->transport,
                        'timeout'  => $this->timeout,
                    ]);

                    // Create connection
                    $connection = new LongPollConnection($http);
                    $connection->setSerializer($serializer);

                    // Notify client
                    $client->processOpen($connection);

                    $this->processReceive($http, $client, $serializer);
                } else {
                    $client->processError(new \Exception());
                }
            },
            function (RequestException $exception) use ($client) {
                $client->processError($exception);
                $client->processClose('unreachable');
            }
        );
    }

    private function processOpen(HttpClient $http, Client $client, Serializer $serializer)
    {

    }

    private function processReceive(HttpClient $http, Client $client, Serializer $serializer, $transport)
    {
        $promise = $this->request($http, $transport . '/receive');

        $promise->then(function (ResponseInterface $response) use ($http, $client, $serializer, $transport) {
            $client->processMessageReceived($serializer->deserialize((string) $response->getBody()));

            $this->processReceive($http, $client, $serializer, $transport);
        });
    }

    private function request(HttpClient $http, $uri, $data = null)
    {
        $this->logger && $this->logger->debug('Request to {uri}', ['uri' => $uri]);

        return new Promise(function ($resolve, $reject) use ($http, $uri, $data) {
            $request = $http->request('POST', $this->uri . $uri);

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