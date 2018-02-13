<?php

namespace PE\Component\WAMP\Client\Transport;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\RequestException;
use PE\Component\WAMP\Client\Client;
use PE\Component\WAMP\Serializer\Serializer;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use React\EventLoop\LoopInterface;
use React\HttpClient\Response;
use React\Promise\Promise;

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
        $url = ($this->secure ? 'https' : 'http') . '://' . $this->host . ':' . $this->port;

        $this->logger && $this->logger->info('Connecting to {url} ...', ['url' => $url]);

        $http = new \React\HttpClient\Client($loop);

        $request = $http->request('POST', $url . '/open');
        $request->on('response', function (Response $response) use ($client) {
            $buffer = '';

            $response->on('data', function ($chunk) use (&$buffer) {
                $buffer .= $chunk;
            });
            $response->on('end', function() use ($client) {
                echo 'DONE';

                $connection = new LongPollConnection();
                $connection->setSerializer(new Serializer());

                $client->processOpen($connection);
            });
        });
        $request->on('error', function (\Exception $e) use ($client) {
            $client->processError($e);
        });
        $request->end();

        return;

        $http = new GuzzleHttpClient([
            'base_uri' => $url,
            'timeout' => $this->timeout
        ]);

        $promise = $http->requestAsync('POST', 'open', ['body' => '{"protocols": ["wamp.2.json"]}']);
        $promise->then(
            function (ResponseInterface $response) use ($client, $loop, $url) {
                $json = json_decode((string) $response->getBody());

                if (isset($json->protocol, $json->transport)) {
                    $serializer = new Serializer();

                    // Create HTTP client with configured base url
                    $http = new GuzzleHttpClient([
                        'base_uri' => $url . '/' . $json->transport . '/',
                        'timeout'  => $this->timeout,
                    ]);

                    // Create periodic timer for check new messages
                    $timer = $loop->addPeriodicTimer(5, function () use ($client, $serializer, $http) {
                        $promise = $http->requestAsync('POST', 'receive');
                        $promise->then(
                            function (ResponseInterface $response) use ($client, $serializer) {
                                $client->processMessageReceived($serializer->deserialize((string) $response->getBody()));
                            },
                            function (RequestException $exception) use ($client) {
                                $client->processError($exception);
                            }
                        );
                    });

                    // Create connection
                    $connection = new LongPollConnection($http, $timer);
                    $connection->setSerializer($serializer);

                    // Notify client
                    $client->processOpen($connection);
                } else {
                    $client->processError(new \Exception());//TODO invalid response
                }
            },
            function (RequestException $exception) use ($client) {
                $client->processError($exception);
                $client->processClose('unreachable');
            }
        );
    }

    private function processOpen(Client $client)
    {
        $connection = new LongPollConnection();//TODO set send callback
        $connection->setSerializer(new Serializer());

        $client->processOpen($connection);

        $this->processReceive();//TODO start recieve loop
    }
}