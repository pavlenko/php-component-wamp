<?php
namespace PE\Component\WAMP;

use PE\Component\WAMP\Client\Client;
use PE\Component\WAMP\Client\Transport\LongPollTransport;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

require_once __DIR__ . '/vendor/autoload.php';

$logger = new ConsoleLogger(new ConsoleOutput(OutputInterface::VERBOSITY_DEBUG));

$transport = new LongPollTransport();
$transport->setLogger($logger);

$client = new Client('realm1');
$client->setTransport($transport);
$client->setLogger($logger);

$client->connect();