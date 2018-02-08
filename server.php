<?php
namespace PE\Component\WAMP;

use Thruway\Peer\Router;
use Thruway\Transport\RatchetTransportProvider;

require_once __DIR__ . '/vendor/autoload.php';

$router = new Router();
$router->addTransportProvider(new RatchetTransportProvider('127.0.0.1', 1337));
$router->start();