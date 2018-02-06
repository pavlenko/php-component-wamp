<?php

namespace PE\Component\WAMP\Client;

use PE\Component\WAMP\Client\Event\Events;
use PE\Component\WAMP\Client\Event\MessageEvent;
use PE\Component\WAMP\Connection\ConnectionInterface;
use PE\Component\WAMP\Message\Message;

class Session extends \PE\Component\WAMP\Session
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(ConnectionInterface $connection, Client $client)
    {
        parent::__construct($connection);
        $this->client = $client;
    }

    /**
     * @inheritDoc
     */
    public function send(Message $message)
    {
        $this->client->getDispatcher()->dispatch(Events::MESSAGE_SEND, new MessageEvent($this, $message));

        $this->getConnection()->send($message);
    }

    /**
     * @param string     $topic
     * @param callable   $callback
     * @param array|null $options
     */
    public function subscribe($topic, callable $callback, array $options = null)
    {
        $this->client->getSubscriber()->subscribe($this, $topic, $callback, $options);
    }

    /**
     * @param string   $topic
     * @param callable $callback
     */
    public function unsubscribe($topic, callable $callback)
    {
        $this->client->getSubscriber()->unsubscribe($this, $topic, $callback);
    }

    /**
     * @param string     $topicName
     * @param array|null $arguments
     * @param array|null $argumentsKw
     * @param array|null $options
     */
    public function publish($topicName, $arguments = null, $argumentsKw = null, array $options = null)
    {
        $this->client->getPublisher()->publish($this, $topicName, $arguments, $argumentsKw, $options);
    }

    /**
     * @param string     $procedureURI
     * @param callable   $callback
     * @param array|null $options
     */
    public function register($procedureURI, callable $callback, array $options = null)
    {
        $this->client->getCallee()->register($this, $procedureURI, $callback, $options);
    }

    /**
     * @param string $procedureURI
     */
    public function unregister($procedureURI)
    {
        $this->client->getCallee()->unregister($this, $procedureURI);
    }

    /**
     * @param string     $procedureURI
     * @param array|null $arguments
     * @param array|null $argumentsKw
     * @param array|null $options
     */
    public function call($procedureURI, $arguments = null, $argumentsKw = null, $options = null)
    {
        $this->client->getCaller()->call($this, $procedureURI, $arguments, $argumentsKw, $options);
    }

    /**
     * @param string     $procedureIRI
     * @param array|null $options
     */
    public function cancel($procedureIRI, array $options = null)
    {
        $this->client->getCaller()->cancel($this, $procedureIRI, $options);
    }
}