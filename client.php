<?php

namespace PE\Component\WAMP;

use PE\Component\WAMP\Client\Client;
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
    new ConsoleLogger(new ConsoleOutput(OutputInterface::VERBOSITY_VERY_VERBOSE))
);

$client->start();