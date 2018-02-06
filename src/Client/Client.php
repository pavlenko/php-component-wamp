<?php

namespace PE\Component\WAMP\Client;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use PE\Component\WAMP\Client\Event\ConnectionEvent;
use PE\Component\WAMP\Client\Event\Events;
use PE\Component\WAMP\Client\Event\MessageEvent;
use PE\Component\WAMP\Client\Role\Callee;
use PE\Component\WAMP\Client\Role\Caller;
use PE\Component\WAMP\Client\Role\Publisher;
use PE\Component\WAMP\Client\Role\RoleInterface;
use PE\Component\WAMP\Client\Role\Subscriber;
use PE\Component\WAMP\Client\Transport\TransportInterface;
use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\ErrorURI;
use PE\Component\WAMP\EventDispatcher\EventDispatcherTrait;
use PE\Component\WAMP\Message\AbortMessage;
use PE\Component\WAMP\Message\GoodbyeMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\WelcomeMessage;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Client implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    use EventDispatcherTrait;

    /**
     * @var string
     */
    private $realm;

    /**
     * @var TransportInterface
     */
    private $transport;

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

    public function __construct($realm, TransportInterface $transport, LoopInterface $loop = null)
    {
        $this->realm     = $realm;
        $this->transport = $transport;
        $this->loop      = $loop ?: Factory::create();

        $this->dispatcher = new EventDispatcher();
    }

    /**
     * Handle connection open (called directly from transport)
     *
     * @param ConnectionInterface $connection
     */
    public function onOpen(ConnectionInterface $connection)
    {
        $this->session = new Session($connection, $this);

        $this->emit(Events::CONNECTION_OPEN, new ConnectionEvent($this->session));

        $this->session->send(new HelloMessage($this->realm, []));
    }

    /**
     * Handle connection close (called directly from transport)
     *
     * @param string $reason
     */
    public function onClose($reason)
    {
        if ($this->session) {
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
    public function onMessage(Message $message)
    {
        //TODO handle authentication
        //TODO handle authorization

        switch (true) {
            case ($message instanceof WelcomeMessage):
                $this->session->setSessionID($message->getSessionId());
                break;
            case ($message instanceof AbortMessage):
                $this->session->shutdown();
                break;
            case ($message instanceof GoodbyeMessage):
                $this->session->send(new GoodbyeMessage([], ErrorURI::_GOODBYE_AND_OUT));
                $this->session->shutdown();
                break;
            default:
                $this->emit(Events::MESSAGE_RECEIVED, new MessageEvent($this->session, $message));
        }
    }

    /**
     * Handle connection error (called directly from transport)
     *
     * @param $error
     */
    public function onError($error)
    {
        $this->emit(Events::CONNECTION_ERROR, new ConnectionEvent($this->session));
    }

    /**
     * @param bool $startLoop
     */
    public function start($startLoop = true)
    {
        $this->transport->start($this, $this->loop);

        if ($startLoop) {
            $this->loop->run();
        }
    }

    private function reconnect()
    {
        //TODO
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
     * @param string $class
     *
     * @return RoleInterface
     *
     * @throws \RuntimeException If role not used
     */
    public function getRole($class)
    {
        if (!array_key_exists($class, $this->roles)) {
            throw new \RuntimeException(sprintf('Unknown role "%s"', $class));
        }

        return $this->roles[$class];
    }

    /**
     * @return Callee|RoleInterface
     */
    public function getCallee()
    {
        return $this->getRole(Callee::class);
    }

    /**
     * @return Caller|RoleInterface
     */
    public function getCaller()
    {
        return $this->getRole(Caller::class);
    }

    /**
     * @return Publisher|RoleInterface
     */
    public function getPublisher()
    {
        return $this->getRole(Publisher::class);
    }

    /**
     * @return Subscriber|RoleInterface
     */
    public function getSubscriber()
    {
        return $this->getRole(Subscriber::class);
    }
}