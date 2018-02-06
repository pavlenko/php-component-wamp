<?php

namespace PE\Component\WAMP\Client\Role;

use PE\Component\WAMP\Client\Event\Events;
use PE\Component\WAMP\Client\Event\MessageEvent;
use PE\Component\WAMP\Client\Session;
use PE\Component\WAMP\Message\ErrorMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\PublishedMessage;
use PE\Component\WAMP\Message\PublishMessage;
use PE\Component\WAMP\Util;

class Publisher implements RoleInterface
{
    /**
     * @var array
     */
    private $requests = [];

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
        $message = $event->getMessage();

        switch (true) {
            case ($message instanceof PublishedMessage):
                $this->processPublishedMessage($message);
                break;
            case ($message instanceof ErrorMessage):
                $this->processErrorMessage($message);
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
     * @param Session $session
     * @param string  $topic
     * @param array   $arguments
     * @param array   $argumentsKw
     * @param array   $options
     */
    public function publish(Session $session, $topic, array $arguments, array $argumentsKw, array $options)
    {
        $requestID = Util::generateID();

        if (isset($options['acknowledge']) && true === $options['acknowledge']) {
            $this->requests[$requestID] = true;
        }

        $session->send(new PublishMessage($requestID, $options, $topic, $arguments, $argumentsKw));
    }

    /**
     * @param PublishedMessage $message
     */
    private function processPublishedMessage(PublishedMessage $message)
    {
        if (isset($this->requests[$message->getRequestID()])) {
            unset($this->requests[$message->getRequestID()]);
        }
    }

    /**
     * @param ErrorMessage $message
     */
    private function processErrorMessage(ErrorMessage $message)
    {
        if (isset($this->requests[$message->getErrorRequestID()])) {
            unset($this->requests[$message->getErrorRequestID()]);
        }
    }
}