<?php

namespace PE\Component\WAMP\Router;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\ErrorURI;
use PE\Component\WAMP\EventDispatcher\EventDispatcherTrait;
use PE\Component\WAMP\Message\GoodbyeMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Router\Event\ConnectionEvent;
use PE\Component\WAMP\Router\Event\Events;
use PE\Component\WAMP\Router\Event\MessageEvent;
use PE\Component\WAMP\Router\Role\RoleInterface;
use PE\Component\WAMP\Router\Transport\TransportInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Router implements LoggerAwareInterface
{
    use EventDispatcherTrait;

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
     * @var \SplObjectStorage|Session[]
     */
    private $sessions;

    public function __construct(LoopInterface $loop = null)
    {
        $this->loop      = $loop ?: Factory::create();

        $this->dispatcher = new EventDispatcher();
        $this->sessions   = new \SplObjectStorage();
    }

    /**
     * Handle connection open (called directly from transport)
     *
     * @param ConnectionInterface $connection
     */
    public function onOpen(ConnectionInterface $connection)
    {
        $session = new Session($connection, $this);

        $this->sessions->attach($connection, $session);

        $this->emit(Events::CONNECTION_OPEN, new ConnectionEvent($session));
    }

    /**
     * Handle connection close (called directly from transport)
     *
     * @param ConnectionInterface $connection
     */
    public function onClose(ConnectionInterface $connection)
    {
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
    public function onMessage(ConnectionInterface $connection, Message $message)
    {
        //TODO handle authentication
        //TODO handle authorization

        $session = $this->sessions[$connection];

        switch (true) {
            case ($message instanceof GoodbyeMessage):
                $session->send(new GoodbyeMessage([], ErrorURI::_GOODBYE_AND_OUT));
                $session->shutdown();
                break;
            default:
                $this->emit(Events::MESSAGE_RECEIVED, new MessageEvent($session, $message));
        }
    }

    /**
     * Handle connection error (called directly from transport)
     *
     * @param ConnectionInterface $connection
     * @param \Exception          $exception
     */
    public function onError(ConnectionInterface $connection, \Exception $exception)
    {
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

        $this->logger && $this->logger->info('Start router');

        $this->transport->start($this, $this->loop);

        if ($startLoop) {
            $this->loop->run();
        }
    }

    public function stop()
    {
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
     * @return EventDispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }
}