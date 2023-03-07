<?php
namespace PE\Component\WAMP;

use PE\Component\WAMP\Router\Authentication\AuthenticationModule;
use PE\Component\WAMP\Router\Authentication\Method\TicketMethod;
use PE\Component\WAMP\Router\Router;
use PE\Component\WAMP\Router\Transport\WebSocketTransport;
use React\EventLoop\Loop;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

require_once __DIR__ . '/vendor/autoload.php';

$logger = new ConsoleLogger(new ConsoleOutput(OutputInterface::VERBOSITY_DEBUG));

$transport = new WebSocketTransport('127.0.0.1', 1337);

$router = new Router(new Factory(), Loop::get(), null, $logger);
$router->setTransport($transport);

$authentication = new AuthenticationModule();
$authentication->addMethod(new TicketMethod('foo'));

$router->addModule($authentication);

$router->start();
