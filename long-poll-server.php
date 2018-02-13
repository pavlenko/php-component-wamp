<?php
namespace PE\Component\WAMP;

use PE\Component\WAMP\Router\Router;
use PE\Component\WAMP\Router\Transport\LongPollTransport;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

require_once __DIR__ . '/vendor/autoload.php';

$logger = new ConsoleLogger(new ConsoleOutput(OutputInterface::VERBOSITY_DEBUG));

$transport = new LongPollTransport();
$transport->setLogger($logger);

$router = new Router();
$router->setTransport($transport);
$router->setLogger($logger);

$router->start();