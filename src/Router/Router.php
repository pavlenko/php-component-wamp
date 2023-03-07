<?php

namespace PE\Component\WAMP\Router;

use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\Events;
use PE\Component\WAMP\FactoryInterface;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Router\Session\SessionModule;
use PE\Component\WAMP\Router\Transport\TransportInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use React\EventLoop\LoopInterface;

final class Router
{
    use Events;

    const EVENT_CONNECTION_OPEN  = 'wamp.router.connection_open';
    const EVENT_CONNECTION_CLOSE = 'wamp.router.connection_close';
    const EVENT_CONNECTION_ERROR = 'wamp.router.connection_error';
    const EVENT_MESSAGE_RECEIVED = 'wamp.router.message_received';
    const EVENT_MESSAGE_SEND     = 'wamp.router.message_send';

    private ?TransportInterface $transport = null;
    private FactoryInterface $factory;
    private LoopInterface $loop;
    private LoggerInterface $logger;

    /**
     * @var RouterModuleInterface[]
     */
    private array $modules = [];

    /**
     * @var \SplObjectStorage|Session[]
     */
    private $sessions;

    public function __construct(FactoryInterface $factory, LoopInterface $loop, LoggerInterface $logger = null)
    {
        $this->factory  = $factory;
        $this->loop     = $loop;
        $this->logger   = $logger ?: new NullLogger();
        $this->sessions = new \SplObjectStorage();

        $this->addModule(new SessionModule());
    }

    public function processOpen(ConnectionInterface $connection): void
    {
        $this->logger->info('Router: open');

        $session = $this->factory->createRouterSession($connection, $this);

        $this->sessions->attach($connection, $session);

        $this->emit(self::EVENT_CONNECTION_OPEN, $session);
    }

    public function processClose(ConnectionInterface $connection): void
    {
        $this->logger->info('Router: close');

        $session = $this->sessions[$connection];

        $this->sessions->detach($connection);

        unset($this->sessions[$connection]);

        $this->emit(self::EVENT_CONNECTION_CLOSE, $session);
    }

    public function processMessageReceived(ConnectionInterface $connection, Message $message): void
    {
        $this->logger->info("Router: {$message->getName()} received");
        $this->logger->debug(json_encode($message));

        $session = $this->sessions[$connection];

        $this->emit(self::EVENT_MESSAGE_RECEIVED, $message, $session);
    }

    public function processMessageSend(ConnectionInterface $connection, Message $message): void
    {
        $this->logger->info("Router: {$message->getName()} send");

        $session = $this->sessions[$connection];

        $this->emit(self::EVENT_MESSAGE_SEND, $message, $session);
        $this->logger->debug(json_encode($message));
    }

    public function processError(ConnectionInterface $connection, \Throwable $exception): void
    {
        $this->logger->error("Router: [{$exception->getCode()}] {$exception->getMessage()}");
        $this->logger->debug("\n{$exception->getTraceAsString()}");

        $this->emit(self::EVENT_CONNECTION_ERROR, $this->sessions[$connection]);
    }

    public function setTransport(TransportInterface $transport): void
    {
        $this->transport = $transport;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function start(bool $startLoop = true): void
    {
        if (null === $this->transport) {
            throw new \RuntimeException('Transport not set via setTransport()');
        }

        $this->logger->info('Router: start');
        $this->transport->start($this, $this->loop);

        if ($startLoop) {
            $this->loop->run();
        }
    }

    public function stop(): void
    {
        $this->logger->info('Router: stop');
        $this->transport->stop();
    }

    public function addModule(RouterModuleInterface $module): void
    {
        $hash = spl_object_hash($module);
        if (array_key_exists($hash, $this->modules)) {
            throw new \InvalidArgumentException('Cannot add same module twice');
        }

        $module->attach($this);
        $this->modules[$hash] = $module;
    }
}
