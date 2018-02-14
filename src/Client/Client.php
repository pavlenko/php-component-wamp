<?php

namespace PE\Component\WAMP\Client;

use PE\Component\WAMP\Client\Session\SessionModule;
use PE\Component\WAMP\Module\ModuleInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use PE\Component\WAMP\Client\Event\ConnectionEvent;
use PE\Component\WAMP\Client\Event\Events;
use PE\Component\WAMP\Client\Event\MessageEvent;
use PE\Component\WAMP\Client\Transport\TransportInterface;
use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\Message;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\GenericEvent;

final class Client
{
    const RECONNECT_TIMEOUT  = 1.5;
    const RECONNECT_ATTEMPTS = 15;

    /**
     * @var string
     */
    private $realm;

    /**
     * @var TransportInterface
     */
    private $transport;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $reconnectTimeout = self::RECONNECT_TIMEOUT;

    /**
     * @var int
     */
    private $reconnectAttempts = self::RECONNECT_ATTEMPTS;

    /**
     * @var int
     */
    private $_reconnectAttempt = 0;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var ModuleInterface[]
     */
    private $modules = [];

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * @param string             $realm
     * @param LoopInterface|null $loop
     */
    public function __construct($realm, LoopInterface $loop = null)
    {
        $this->realm = $realm;
        $this->loop  = $loop ?: Factory::create();

        $this->dispatcher = new EventDispatcher();

        $this->addModule(new SessionModule());
    }

    /**
     * Handle connection open (called directly from transport)
     *
     * @param ConnectionInterface $connection
     */
    public function processOpen(ConnectionInterface $connection)
    {
        $this->_reconnectAttempt = 0;
        !$this->logger ?: $this->logger->info('Connection opened');

        $this->session = new Session($connection, $this);

        $this->emit(Events::CONNECTION_OPEN, new ConnectionEvent($this->session));

        $this->session->send(new HelloMessage($this->realm, []));
    }

    /**
     * Handle connection close (called directly from transport)
     *
     * @param string $reason
     */
    public function processClose($reason)
    {
        if ($this->session) {
            $this->logger && $this->logger->info('Client: close: ' . $reason);

            $this->emit(Events::CONNECTION_CLOSE, new ConnectionEvent($this->session));

            $this->session->shutdown();
            $this->session = null;
        }

        $this->reconnect();
    }

    /**
     * Handle received message (called directly from transport)
     *
     * @param Message $message
     */
    public function processMessageReceived(Message $message)
    {
        $this->logger && $this->logger->info('> ' . $message->getName());
        $this->logger && $this->logger->debug(json_encode($message));

        $this->emit(Events::MESSAGE_RECEIVED, new MessageEvent($this->session, $message));
    }

    /**
     * Handle received message (called directly from transport)
     *
     * @param Message $message
     */
    public function processMessageSend(Message $message)
    {
        $this->logger && $this->logger->info('< ' . $message->getName());
        $this->logger && $this->logger->debug(json_encode($message));

        $this->emit(Events::MESSAGE_SEND, new MessageEvent($this->session, $message));
    }

    /**
     * Handle connection error (called directly from transport)
     *
     * @param \Exception $ex
     */
    public function processError(\Exception $ex)
    {
        $this->logger && $this->logger->error("Client: [{$ex->getCode()}] {$ex->getMessage()}");
        $this->logger && $this->logger->debug($ex->getTraceAsString());

        $this->emit(Events::CONNECTION_ERROR, new ConnectionEvent($this->session));
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

    /**
     * @inheritDoc
     */
    public function setReconnectTimeout($timeout)
    {
        $this->reconnectTimeout = (int) $timeout;
    }

    /**
     * @inheritDoc
     */
    public function setReconnectAttempts($attempts)
    {
        $this->reconnectAttempts = (int) $attempts;
    }

    /**
     * @param bool $startLoop
     *
     * @throws \RuntimeException
     */
    public function connect($startLoop = true)
    {
        if (null === $this->transport) {
            throw new \RuntimeException('Transport not set via setTransport()');
        }

        $this->logger && $this->logger->info('Client: connecting...');

        $this->transport->start($this, $this->loop);

        if ($startLoop) {
            $this->loop->run();
        }
    }

    /**
     * Reconnect logic
     */
    private function reconnect()
    {
        if ($this->reconnectAttempts <= $this->_reconnectAttempt) {
            // Max retry attempts reached
            $this->logger && $this->logger->error("Client: unable to connect after {$this->reconnectAttempts} attempts");
            return;
        }

        $this->logger && $this->logger->warning("Client: reconnect after {$this->reconnectTimeout} seconds");

        $this->_reconnectAttempt++;

        $this->loop->addTimer($this->reconnectTimeout, function () {
            $this->transport->start($this, $this->loop);
        });
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