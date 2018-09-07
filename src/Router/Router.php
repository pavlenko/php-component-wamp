<?php

namespace PE\Component\WAMP\Router;

use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\Events;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Router\Session\SessionModule;
use PE\Component\WAMP\Router\Transport\TransportInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;

final class Router implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    use Events;

    const EVENT_CONNECTION_OPEN  = 'wamp.router.connection_open';
    const EVENT_CONNECTION_CLOSE = 'wamp.router.connection_close';
    const EVENT_CONNECTION_ERROR = 'wamp.router.connection_error';
    const EVENT_MESSAGE_RECEIVED = 'wamp.router.message_received';
    const EVENT_MESSAGE_SEND     = 'wamp.router.message_send';

    /**
     * @var TransportInterface
     */
    private $transport;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var RouterModuleInterface[]
     */
    private $modules = [];

    /**
     * @var \SplObjectStorage|Session[]
     */
    private $sessions;

    public function __construct(LoopInterface $loop = null)
    {
        $this->loop     = $loop ?: Factory::create();
        $this->sessions = new \SplObjectStorage();

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

        $this->emit(self::EVENT_CONNECTION_OPEN, $session);
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

        $this->emit(self::EVENT_CONNECTION_CLOSE, $session);
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

        $this->emit(self::EVENT_MESSAGE_RECEIVED, $message, $session);
    }

    /**
     * @param ConnectionInterface $connection
     * @param Message             $message
     */
    public function processMessageSend(ConnectionInterface $connection, Message $message)
    {
        $this->logger && $this->logger->info("Router: {$message->getName()} send");

        $session = $this->sessions[$connection];

        $this->emit(self::EVENT_MESSAGE_SEND, $message, $session);

        $this->logger && $this->logger->debug(json_encode($message));
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
        $this->logger && $this->logger->debug("\n{$ex->getTraceAsString()}");

        $this->emit(self::EVENT_CONNECTION_ERROR, $this->sessions[$connection]);
    }

    /**
     * @inheritDoc
     */
    public function setTransport(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
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
     * @param RouterModuleInterface $module
     *
     * @throws \InvalidArgumentException
     */
    public function addModule(RouterModuleInterface $module)
    {
        if (array_key_exists($hash = spl_object_hash($module), $this->modules)) {
            throw new \InvalidArgumentException('Cannot add same module twice');
        }

        $module->subscribe($this);

        $this->modules[$hash] = $module;
    }
}