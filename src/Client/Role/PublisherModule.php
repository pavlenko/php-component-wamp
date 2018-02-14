<?php

namespace PE\Component\WAMP\Client\Role;

use PE\Component\WAMP\Client\ClientModuleInterface;
use PE\Component\WAMP\Client\Event\Events;
use PE\Component\WAMP\Client\Event\MessageEvent;
use PE\Component\WAMP\Message\ErrorMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\PublishedMessage;
use PE\Component\WAMP\Session;
use React\Promise\Deferred;

class PublisherModule implements ClientModuleInterface
{
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