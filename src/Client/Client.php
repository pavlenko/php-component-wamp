<?php

namespace PE\Component\WAMP\Client;

use Psr\Log\LoggerInterface;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use PE\Component\WAMP\Client\Event\ConnectionEvent;
use PE\Component\WAMP\Client\Event\Events;
use PE\Component\WAMP\Client\Event\MessageEvent;
use PE\Component\WAMP\Client\Role\RoleInterface;
use PE\Component\WAMP\Client\Transport\TransportInterface;
use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\ErrorURI;
use PE\Component\WAMP\Message\AbortMessage;
use PE\Component\WAMP\Message\GoodbyeMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\WelcomeMessage;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class Client extends EventDispatcher implements ClientInterface
{
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
     * @var RoleInterface[]
     */
    private $roles = [];

    public function __construct(
        $realm,
        LoopInterface $loop = null
    ) {
        $this->realm = $realm;
        $this->loop  = $loop ?: Factory::create();

        //TODO move to debug subscriber (client module interface)
        $this->addListener(Events::MESSAGE_SEND, function (MessageEvent $event) {
            $this->logger->info('< ' . $event->getMessage()->getName());
        });
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Handle connection open (called directly from transport)
     *
     * @param ConnectionInterface $connection
     */
    public function onOpen(ConnectionInterface $connection)
    {
        $this->_reconnectAttempt = 0;
        !$this->logger ?: $this->logger->info('Connection opened');

        $this->session = new Session($connection, $this);

        $this->dispatch(Events::CONNECTION_OPEN, new ConnectionEvent($this->session));

        $this->session->send(new HelloMessage($this->realm, []));
    }

    /**
     * Handle connection close (called directly from transport)
     *
     * @param string $reason
     */
    public function onClose($reason)
    {
        !$this->logger ?: $this->logger->info('Connection closed: ' . $reason);

        if ($this->session) {
            $this->dispatch(Events::CONNECTION_CLOSE, new ConnectionEvent($this->session));

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
    public function onMessageReceived(Message $message)
    {
        $this->logger->info('> ' . $message->getName());
        //TODO handle authentication
        //TODO handle authorization

        switch (true) {
            case ($message instanceof WelcomeMessage):
                $this->session->setSessionID($message->getSessionId());
                $this->dispatch(Events::SESSION_ESTABLISHED, new ConnectionEvent($this->session));
                break;
            case ($message instanceof AbortMessage):
                $this->session->shutdown();
                break;
            case ($message instanceof GoodbyeMessage):
                $this->session->send(new GoodbyeMessage([], ErrorURI::_GOODBYE_AND_OUT));
                $this->session->shutdown();
                break;
            default:
                $this->dispatch(Events::MESSAGE_RECEIVED, new MessageEvent($this->session, $message));
        }
    }

    /**
     * @inheritDoc
     */
    public function onMessageSend(Message $message)
    {
        // TODO: Implement onMessageSend() method.
    }

    /**
     * Handle connection error (called directly from transport)
     *
     * @param \Exception $error
     */
    public function onError(\Exception $error)
    {
        $this->logger->error($error->getMessage());
        $this->dispatch(Events::CONNECTION_ERROR, new ConnectionEvent($this->session));
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
     */
    public function connect($startLoop = true)
    {
        if (null === $this->transport) {
            throw new \RuntimeException('Transport not set via setTransport()');
        }

        //$this->logger->info('Starting transport');
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
            !$this->logger ?: $this->logger->error('Unable to connect after {n} attempts', ['n' => $this->reconnectAttempts]);
            return;
        }

        !$this->logger ?: $this->logger->warning('Reconnect after {n} seconds', ['n' => $this->reconnectTimeout]);

        $this->_reconnectAttempt++;

        $this->loop->addTimer($this->reconnectTimeout, function () {
            $this->transport->start($this, $this->loop);
        });
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

        $this->addSubscriber($role);
    }
}