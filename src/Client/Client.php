<?php

namespace PE\Component\WAMP\Client;

use PE\Component\WAMP\Client\Session\SessionModule;
use PE\Component\WAMP\Client\Transport\TransportInterface;
use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\Events;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\Message;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;

final class Client implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    use Events;

    const RECONNECT_TIMEOUT  = 1.5;
    const RECONNECT_ATTEMPTS = 15;

    const EVENT_CONNECTION_OPEN     = 'wamp.client.connection_open';
    const EVENT_CONNECTION_CLOSE    = 'wamp.client.connection_close';
    const EVENT_CONNECTION_ERROR    = 'wamp.client.connection_error';
    const EVENT_SESSION_ESTABLISHED = 'wamp.client.session_established';
    const EVENT_MESSAGE_RECEIVED    = 'wamp.client.message_received';
    const EVENT_MESSAGE_SEND        = 'wamp.client.message_send';

    /**
     * @var string
     */
    private string $realm;

    /**
     * @var TransportInterface
     */
    private TransportInterface $transport;

    /**
     * @var float
     */
    private float $reconnectTimeout = self::RECONNECT_TIMEOUT;

    /**
     * @var int
     */
    private int $reconnectAttempts = self::RECONNECT_ATTEMPTS;

    /**
     * @var int
     */
    private int $_reconnectAttempt = 0;

    /**
     * @var LoopInterface
     */
    private LoopInterface $loop;

    /**
     * @var Session|null
     */
    private ?Session $session;

    /**
     * @var ClientModuleInterface[]
     */
    private array $modules = [];

    /**
     * @param string $realm
     * @param LoopInterface|null $loop
     */
    public function __construct(string $realm, LoopInterface $loop = null)
    {
        $this->realm = $realm;
        $this->loop  = $loop ?: Factory::create();

        $this->addModule(new SessionModule());
    }

    /**
     * Handle connection open (called directly from transport)
     *
     * @param ConnectionInterface $connection
     */
    public function processOpen(ConnectionInterface $connection): void
    {
        $this->_reconnectAttempt = 0;
        $this->logger && $this->logger->info('Connection opened');

        $this->session = new Session($connection, $this);

        $this->emit(self::EVENT_CONNECTION_OPEN, $this->session);

        $this->session->send(new HelloMessage($this->realm, []));
    }

    /**
     * Handle connection close (called directly from transport)
     *
     * @param string $reason
     */
    public function processClose(string $reason): void
    {
        if ($this->session) {
            $this->logger && $this->logger->info('Client: close: ' . $reason);

            $this->emit(self::EVENT_CONNECTION_CLOSE, $this->session);

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
    public function processMessageReceived(Message $message): void
    {
        $this->logger && $this->logger->info("Client: {$message->getName()} received");
        $this->logger && $this->logger->debug(json_encode($message));

        $this->emit(self::EVENT_MESSAGE_RECEIVED, $message, $this->session);
    }

    /**
     * Handle received message (called directly from transport)
     *
     * @param Message $message
     */
    public function processMessageSend(Message $message): void
    {
        $this->logger && $this->logger->info("Client: {$message->getName()} send");

        $this->emit(self::EVENT_MESSAGE_SEND, $message, $this->session);

        $this->logger && $this->logger->debug(json_encode($message));
    }

    /**
     * Handle connection error (called directly from transport)
     *
     * @param \Exception $ex
     */
    public function processError(\Exception $ex): void
    {
        $this->logger && $this->logger->error("Client: [{$ex->getCode()}] {$ex->getMessage()}");
        $this->logger && $this->logger->debug("\n{$ex->getTraceAsString()}");

        $this->emit(self::EVENT_CONNECTION_ERROR, $this->session);
    }

    /**
     * @param TransportInterface $transport
     */
    public function setTransport(TransportInterface $transport): void
    {
        $this->transport = $transport;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param float $timeout
     */
    public function setReconnectTimeout(float $timeout): void
    {
        $this->reconnectTimeout = $timeout;
    }

    /**
     * @param int $attempts
     */
    public function setReconnectAttempts(int $attempts): void
    {
        $this->reconnectAttempts = $attempts;
    }

    /**
     * @param bool $startLoop
     *
     * @throws \RuntimeException
     */
    public function connect(bool $startLoop = true): void
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
    private function reconnect(): void
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
     * @param ClientModuleInterface $module
     *
     * @throws \InvalidArgumentException
     */
    public function addModule(ClientModuleInterface $module): void
    {
        if (array_key_exists($hash = spl_object_hash($module), $this->modules)) {
            throw new \InvalidArgumentException('Cannot add same module twice');
        }

        $module->subscribe($this);

        $this->modules[$hash] = $module;
    }
}