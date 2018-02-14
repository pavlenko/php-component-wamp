<?php

namespace PE\Component\WAMP\Router;

use PE\Component\WAMP\Module\ModuleInterface;
use PE\Component\WAMP\Router\Session\SessionModule;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Router\Event\ConnectionEvent;
use PE\Component\WAMP\Router\Event\Events;
use PE\Component\WAMP\Router\Event\MessageEvent;
use PE\Component\WAMP\Router\Role\RoleInterface;
use PE\Component\WAMP\Router\Transport\TransportInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\GenericEvent;

final class Router implements LoggerAwareInterface
{
    /**
     * @var TransportInterface
     */
    private $transport;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var RoleInterface[]
     */
    private $roles = [];

    /**
     * @var ModuleInterface[]
     */
    private $modules = [];

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * @var \SplObjectStorage|Session[]
     */
    private $sessions;

    public function __construct(LoopInterface $loop = null)
    {
        $this->loop      = $loop ?: Factory::create();

        $this->dispatcher = new EventDispatcher();
        $this->sessions   = new \SplObjectStorage();

        $this->addModule(new SessionModule());
    }

    /**
     * Handle connection open (called directly from transport)
     *
     * @param ConnectionInterface $connection
     */
    public function processOpen(ConnectionInterface $connection)
    {
        $this->logger && $this->logger->info('Router: open');

        $session = new Session($connection, $this);

        $this->sessions->attach($connection, $session);

        $this->emit(Events::CONNECTION_OPEN, new ConnectionEvent($session));
    }

    /**
     * Handle connection close (called directly from transport)
     *
     * @param ConnectionInterface $connection
     */
    public function processClose(ConnectionInterface $connection)
    {
        $this->logger && $this->logger->info('Router: close');

        $session = $this->sessions[$connection];

        $this->sessions->detach($connection);

        unset($this->sessions[$connection]);

        $this->emit(Events::CONNECTION_CLOSE, new ConnectionEvent($session));
    }

    /**
     * Handle received message (called directly from transport)
     *
     * @param ConnectionInterface $connection
     * @param Message             $message
     */
    public function processMessageReceived(ConnectionInterface $connection, Message $message)
    {
        $this->logger && $this->logger->info("Router: {$message->getName()} received");
        $this->logger && $this->logger->debug(json_encode($message));

        $session = $this->sessions[$connection];

        $this->emit(Events::MESSAGE_RECEIVED, new MessageEvent($session, $message));
    }

    /**
     * @param ConnectionInterface $connection
     * @param Message             $message
     */
    public function processMessageSend(ConnectionInterface $connection, Message $message)
    {
        $this->logger && $this->logger->info("Router: {$message->getName()} send");
        $this->logger && $this->logger->debug(json_encode($message));

        $session = $this->sessions[$connection];

        $this->emit(Events::MESSAGE_SEND, new MessageEvent($session, $message));
    }

    /**
     * Handle connection error (called directly from transport)
     *
     * @param ConnectionInterface $connection
     * @param \Exception          $ex
     */
    public function processError(ConnectionInterface $connection, \Exception $ex)
    {
        $this->logger && $this->logger->error("Router: [{$ex->getCode()}] {$ex->getMessage()}");
        $this->logger && $this->logger->debug($ex->getTraceAsString());

        $this->emit(Events::CONNECTION_ERROR, new ConnectionEvent($this->sessions[$connection]));
    }

    /**
     * @inheritDoc
     */
    public function setTransport(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @inheritDoc
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function start($startLoop = true)
    {
        if (null === $this->transport) {
            throw new \RuntimeException('Transport not set via setTransport()');
        }

        $this->logger && $this->logger->info('Router: start');

        $this->transport->start($this, $this->loop);

        if ($startLoop) {
            $this->loop->run();
        }
    }

    public function stop()
    {
        $this->logger && $this->logger->info('Router: stop');
        $this->transport->stop();
    }

    /**
     * @param RoleInterface $role
     *
     * @throws \InvalidArgumentException If role added twice
     */
    public function addRole(RoleInterface $role)
    {
        $class = get_class($role);

        if (array_key_exists($class, $this->roles)) {
            throw new \InvalidArgumentException(sprintf('Cannot add role "%s" twice', $class));
        }

        $this->roles[$class] = $role;

        $this->dispatcher->addSubscriber($role);
    }

    /**
     * @param ModuleInterface $module
     *
     * @throws \InvalidArgumentException
     */
    public function addModule(ModuleInterface $module)
    {
        if (array_key_exists($hash = spl_object_hash($module), $this->modules)) {
            throw new \InvalidArgumentException('Cannot add same module twice');
        }

        $this->modules[$hash] = $module;

        $this->dispatcher->addSubscriber($module);
    }

    /**
     * @param string   $eventName
     * @param callable $listener
     * @param int      $priority
     */
    public function on($eventName, callable $listener, $priority = 0)
    {
        $this->dispatcher->addListener($eventName, $listener, $priority);
    }

    /**
     * @param string   $eventName
     * @param callable $listener
     */
    public function off($eventName, callable $listener)
    {
        $this->dispatcher->removeListener($eventName, $listener);
    }

    /**
     * @param string   $eventName
     * @param callable $listener
     * @param int      $priority
     */
    public function once($eventName, callable $listener, $priority = 0)
    {
        $onceListener = function () use (&$onceListener, $eventName, $listener) {
            $this->off($eventName, $onceListener);

            \call_user_func_array($listener, \func_get_args());
        };

        $this->on($eventName, $onceListener, $priority);
    }

    /**
     * @param string $eventName
     * @param mixed  $payload
     */
    public function emit($eventName, $payload = null)
    {
        if (null !== $payload && !($payload instanceof Event)) {
            $payload = new GenericEvent($payload);
        }

        $this->dispatcher->dispatch($eventName, $payload);
    }
}