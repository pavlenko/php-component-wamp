<?php

namespace PE\Component\WAMP\Client;

use PE\Component\WAMP\Client\Session\SessionModule;
use PE\Component\WAMP\Client\Transport\TransportInterface;
use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\FactoryInterface;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Util\Events;
use PE\Component\WAMP\Util\EventsInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use React\EventLoop\LoopInterface;

final class Client
{
    const RECONNECT_TIMEOUT  = 1.5;
    const RECONNECT_ATTEMPTS = 15;

    const EVENT_CONNECTION_OPEN     = 'wamp.client.connection_open';
    const EVENT_CONNECTION_CLOSE    = 'wamp.client.connection_close';
    const EVENT_CONNECTION_ERROR    = 'wamp.client.connection_error';
    const EVENT_SESSION_ESTABLISHED = 'wamp.client.session_established';
    const EVENT_MESSAGE_RECEIVED    = 'wamp.client.message_received';
    const EVENT_MESSAGE_SEND        = 'wamp.client.message_send';

    private string $realm;

    private ?TransportInterface $transport = null;

    private float $reconnectTimeout = self::RECONNECT_TIMEOUT;

    private int $reconnectAttempts = self::RECONNECT_ATTEMPTS;

    private int $_reconnectAttempt = 0;

    private FactoryInterface $factory;
    private LoopInterface $loop;
    private EventsInterface $events;
    private LoggerInterface $logger;
    private ?SessionInterface $session = null;

    /**
     * @var ClientModuleInterface[]
     */
    private array $modules = [];

    public function __construct(string $realm, FactoryInterface $factory, LoopInterface $loop, EventsInterface $events = null, LoggerInterface $logger = null)
    {
        $this->realm   = $realm;
        $this->factory = $factory;
        $this->loop    = $loop;
        $this->events  = $events ?: new Events();
        $this->logger  = $logger ?: new NullLogger();

        $this->addModule(new SessionModule());
    }

    public function processOpen(ConnectionInterface $connection): void
    {
        $this->_reconnectAttempt = 0;
        $this->logger->info('Connection opened');

        $this->session = $this->factory->createClientSession($connection, $this);

        $this->events->trigger(self::EVENT_CONNECTION_OPEN, $this->session);

        $this->session->send(new HelloMessage($this->realm, []));
    }

    public function processClose(string $reason): void
    {
        if ($this->session) {
            $this->logger->info('Client: close: ' . $reason);

            $this->events->trigger(self::EVENT_CONNECTION_CLOSE, $this->session);

            $this->session->shutdown();
            $this->session = null;
        }

        $this->reconnect();
    }

    public function processMessageReceived(Message $message): void
    {
        $this->logger->info("Client: {$message->getName()} received");
        $this->logger->debug(json_encode($message));

        $this->events->trigger(self::EVENT_MESSAGE_RECEIVED, $message, $this->session);
    }

    public function processMessageSend(Message $message): void
    {
        $this->logger->info("Client: {$message->getName()} send");
        $this->events->trigger(self::EVENT_MESSAGE_SEND, $message, $this->session);
        $this->logger->debug(json_encode($message));
    }

    public function processError(\Throwable $exception): void
    {
        $this->logger->error("Client: [{$exception->getCode()}] {$exception->getMessage()}");
        $this->logger->debug("\n{$exception->getTraceAsString()}");

        $this->events->trigger(self::EVENT_CONNECTION_ERROR, $this->session);
    }

    public function setTransport(TransportInterface $transport): void
    {
        $this->transport = $transport;
    }

    public function setReconnectTimeout(float $timeout): void
    {
        $this->reconnectTimeout = $timeout;
    }

    public function setReconnectAttempts(int $attempts): void
    {
        $this->reconnectAttempts = $attempts;
    }

    public function connect(bool $startLoop = true): void
    {
        if (null === $this->transport) {
            throw new \RuntimeException('Transport not set via setTransport()');
        }

        $this->logger->info('Client: connecting...');

        $this->transport->start($this, $this->loop, $this->logger);

        if ($startLoop) {
            $this->loop->run();//TODO add some logic for ensure loop running
        }
    }

    private function reconnect(): void
    {
        if ($this->reconnectAttempts <= $this->_reconnectAttempt) {
            // Max retry attempts reached
            $this->logger->error("Client: unable to connect after {$this->reconnectAttempts} attempts");
            return;
        }

        $this->logger->warning("Client: reconnect after {$this->reconnectTimeout} seconds");

        $this->_reconnectAttempt++;

        $this->loop->addTimer($this->reconnectTimeout, function () {
            $this->transport->start($this, $this->loop, $this->logger);
        });
    }


    public function addModule(ClientModuleInterface $module): void
    {
        $hash = spl_object_hash($module);
        if (array_key_exists($hash, $this->modules)) {
            throw new \InvalidArgumentException('Cannot add same module twice');
        }

        $module->attach($this->events);
        $this->modules[$hash] = $module;
    }
}
