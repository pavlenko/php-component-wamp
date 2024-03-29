<?php

namespace PE\Component\WAMP\Router;

use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\FactoryInterface;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Router\Session\SessionInterface;
use PE\Component\WAMP\Router\Session\SessionModule;
use PE\Component\WAMP\Router\Transport\TransportInterface;
use PE\Component\WAMP\Util\Events;
use PE\Component\WAMP\Util\EventsInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;

final class Router implements RouterInterface
{
    public const EVENT_CONNECTION_OPEN  = 'wamp.router.connection_open';
    public const EVENT_CONNECTION_CLOSE = 'wamp.router.connection_close';
    public const EVENT_CONNECTION_ERROR = 'wamp.router.connection_error';
    public const EVENT_MESSAGE_RECEIVED = 'wamp.router.message_received';
    public const EVENT_MESSAGE_SEND     = 'wamp.router.message_send';

    private const PING_INTERVAL = 120;

    private int $pingInterval = self::PING_INTERVAL;
    private ?TimerInterface $pingTimer = null;

    private ?TransportInterface $transport = null;
    private FactoryInterface $factory;
    private EventsInterface $events;
    private LoopInterface $loop;
    private LoggerInterface $logger;

    /**
     * @var string[]
     */
    private array $realms = [];

    /**
     * @var RouterModuleInterface[]
     */
    private array $modules = [];

    /**
     * @var \SplObjectStorage|SessionInterface[]
     */
    private $sessions;

    public function __construct(FactoryInterface $factory, LoopInterface $loop, EventsInterface $events = null, LoggerInterface $logger = null)
    {
        $this->factory  = $factory;
        $this->loop     = $loop;
        $this->events   = $events ?: new Events();
        $this->logger   = $logger ?: new NullLogger();
        $this->sessions = new \SplObjectStorage();

        $this->addModule(new SessionModule());
    }

    public function processOpen(ConnectionInterface $connection): void
    {
        $this->logger->info('Router: open');

        $session = $this->factory->createRouterSession($connection, $this);

        $this->sessions->attach($connection, $session);

        $this->events->trigger(self::EVENT_CONNECTION_OPEN, $session);
    }

    public function processClose(ConnectionInterface $connection): void
    {
        $this->logger->info('Router: close');

        $session = $this->sessions[$connection];

        $this->sessions->detach($connection);

        unset($this->sessions[$connection]);

        $this->events->trigger(self::EVENT_CONNECTION_CLOSE, $session);
    }

    public function processMessageReceived(ConnectionInterface $connection, Message $message): void
    {
        $this->logger->info('<-- ' . $message);
        $this->events->trigger(self::EVENT_MESSAGE_RECEIVED, $message, $this->sessions[$connection], $this);
    }

    public function processMessageSend(ConnectionInterface $connection, Message $message): void
    {
        $this->events->trigger(self::EVENT_MESSAGE_SEND, $message, $this->sessions[$connection]);
        $this->logger->info('--> ' . $message);
    }

    public function processError(ConnectionInterface $connection, \Throwable $exception): void
    {
        $this->logger->error("Router: [{$exception->getCode()}] {$exception->getMessage()}");
        $this->logger->debug("\n{$exception->getTraceAsString()}");

        $this->events->trigger(self::EVENT_CONNECTION_ERROR, $this->sessions[$connection]);
    }

    public function getRealms(): array
    {
        return $this->realms;
    }

    public function setRealms(array $realms): void
    {
        $this->realms = $realms;
    }

    public function setTransport(TransportInterface $transport): void
    {
        $this->transport = $transport;
    }

    public function start(bool $startLoop = true): void
    {
        if (null === $this->transport) {
            throw new \RuntimeException('Transport not set via setTransport()');
        }

        $this->logger->info('Router: start');
        $this->transport->start($this, $this->loop, $this->logger);

        if ($startLoop) {
            $this->pingTimer = $this->loop->addPeriodicTimer($this->pingInterval, function () {
                /* @var $connection ConnectionInterface */
                foreach ($this->sessions as $connection) {
                    $connection->ping();
                }
            });

            $this->loop->run();
        }
    }

    public function stop(): void
    {
        $this->logger->info('Router: stop');
        if ($this->pingTimer) {
            $this->loop->cancelTimer($this->pingTimer);
        }
        $this->transport->stop();
    }

    public function addModule(RouterModuleInterface $module): void
    {
        $hash = spl_object_hash($module);
        if (array_key_exists($hash, $this->modules)) {
            throw new \InvalidArgumentException('Cannot add same module twice');
        }

        $module->attach($this->events);
        $this->modules[$hash] = $module;
    }
}
