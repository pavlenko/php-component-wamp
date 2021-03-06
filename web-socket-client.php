<?php
namespace PE\Component\WAMP;

use PE\Component\WAMP\Client\Authentication\AuthenticationModule;
use PE\Component\WAMP\Client\Authentication\Method\TicketMethod;
use PE\Component\WAMP\Client\Client;
use PE\Component\WAMP\Client\Role\Publisher\PublisherModule;
use PE\Component\WAMP\Client\Role\Publisher\PublisherAPI;
use PE\Component\WAMP\Client\Role\SubscriberModule;
use PE\Component\WAMP\Client\Role\SubscriberAPI;
use PE\Component\WAMP\Client\Session;
use PE\Component\WAMP\Client\Transport\WebSocketTransport;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

require_once __DIR__ . '/vendor/autoload.php';

$logger = new ConsoleLogger(new ConsoleOutput(OutputInterface::VERBOSITY_DEBUG));

$transport = new WebSocketTransport('127.0.0.1', 1337, false, 5);

$client = new Client('realm1');
$client->setTransport($transport);
$client->setLogger($logger);
$client->setReconnectAttempts(3);

$authentication = new AuthenticationModule();
$authentication->addMethod(new TicketMethod('foo'));

$client->addModule($authentication);
$client->addModule(new SubscriberModule());
$client->addModule(new PublisherModule());

$client->on(Client::EVENT_SESSION_ESTABLISHED, function (Session $session) {
    $subscriber = new SubscriberAPI($session);
    $subscriber->subscribe('foo', function () {
        echo json_encode(func_get_args()) . "\n";
    });

    $publisher = new PublisherAPI($session);
    $publisher->publish('foo');
});

$client->connect();
