<?php

namespace PE\Component\WAMP\Client\Role;

use PE\Component\WAMP\Client\Event\Events;
use PE\Component\WAMP\Client\Event\MessageEvent;
use PE\Component\WAMP\Message\ErrorMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\PublishedMessage;
use PE\Component\WAMP\Message\PublishMessage;
use PE\Component\WAMP\Session;
use PE\Component\WAMP\Util;
use React\Promise\Deferred;
use React\Promise\FulfilledPromise;
use React\Promise\PromiseInterface;

class Publisher implements RoleInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @param Session|null $session
     */
    public function __construct(Session $session = null)
    {
        $this->session = $session;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::MESSAGE_RECEIVED => 'onMessageReceived',
            Events::MESSAGE_SEND     => 'onMessageSend',
        ];
    }

    /**
     * @param MessageEvent $event
     */
    public function onMessageReceived(MessageEvent $event)
    {
        $session = $event->getSession();
        $message = $event->getMessage();

        switch (true) {
            case ($message instanceof PublishedMessage):
                $this->processPublishedMessage($session, $message);
                break;
            case ($message instanceof ErrorMessage):
                $this->processErrorMessage($session, $message);
                break;
        }
    }

    /**
     * @param MessageEvent $event
     */
    public function onMessageSend(MessageEvent $event)
    {
        $message = $event->getMessage();

        if ($message instanceof HelloMessage) {
            $message->addFeatures('publisher', [
                //TODO
            ]);
        }
    }

    /**
     * @param string $topic
     * @param array  $arguments
     * @param array  $argumentsKw
     * @param array  $options
     *
     * @return PromiseInterface
     *
     * @throws \InvalidArgumentException
     */
    public function publish($topic, array $arguments, array $argumentsKw, array $options)
    {
        $requestID = Util::generateID();
        $deferred  = null;

        if (isset($options['acknowledge']) && true === $options['acknowledge']) {
            if (!is_array($this->session->publishRequests)) {
                $this->session->publishRequests = [];
            }

            $this->session->publishRequests[$requestID] = $deferred = new Deferred();
        }

        $this->session->send(new PublishMessage($requestID, $options, $topic, $arguments, $argumentsKw));

        return $deferred ? $deferred->promise() : new FulfilledPromise();
    }

    /**
     * @param Session          $session
     * @param PublishedMessage $message
     */
    private function processPublishedMessage(Session $session, PublishedMessage $message)
    {
        if (isset($session->publishRequests[$id = $message->getRequestID()])) {
            /* @var $deferred Deferred */
            $deferred = $session->publishRequests[$id];
            $deferred->resolve();

            unset($session->publishRequests[$id]);
        }
    }

    /**
     * @param Session      $session
     * @param ErrorMessage $message
     */
    private function processErrorMessage(Session $session, ErrorMessage $message)
    {
        if (isset($session->publishRequests[$id = $message->getErrorRequestID()])) {
            /* @var $deferred Deferred */
            $deferred = $session->publishRequests[$id];
            $deferred->resolve();

            unset($session->publishRequests[$id]);
        }
    }
}