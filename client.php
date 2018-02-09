<?php

namespace PE\Component\WAMP;

use PE\Component\WAMP\Client\Client;
use PE\Component\WAMP\Client\Event\ConnectionEvent;
use PE\Component\WAMP\Client\Event\Events;
use PE\Component\WAMP\Client\Role\Publisher;
use PE\Component\WAMP\Client\Role\Subscriber;
use PE\Component\WAMP\Client\Transport\WebSocketTransport;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

require_once __DIR__ . '/vendor/autoload.php';

$client = new Client(
    'realm1',
    new WebSocketTransport('127.0.0.1', 1337, false),
    null,
    null,
    new ConsoleLogger(new ConsoleOutput(OutputInterface::VERBOSITY_DEBUG))
);

$client->addRole(new Subscriber());
$client->addRole(new Publisher());

$client->getDispatcher()->addListener(Events::SESSION_ESTABLISHED, function (ConnectionEvent $event) {
    $session = $event->getSession();

    $session->subscribe('foo', function () {
        echo json_encode(func_get_args()) . "\n";
    });

    $session->publish('foo');
});

$client->connect();