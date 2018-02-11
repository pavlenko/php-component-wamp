<?php

namespace PE\Component\WAMP\Client\Role;

use PE\Component\WAMP\Client\CallCollection;
use PE\Component\WAMP\Client\Event\Events;
use PE\Component\WAMP\Client\Event\MessageEvent;
use PE\Component\WAMP\Message\ErrorMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\ResultMessage;
use PE\Component\WAMP\MessageCode;
use PE\Component\WAMP\Session;

class Caller implements RoleInterface
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
            case ($message instanceof ResultMessage):
                $this->processResultMessage($session, $message);
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
            $message->addFeatures('caller', [
                //TODO
            ]);
        }
    }

    /**
     * @param Session       $session
     * @param ResultMessage $message
     */
    private function processResultMessage(Session $session, ResultMessage $message)
    {
        $calls = $session->callRequests ?: new CallCollection();

        if ($call = $calls->findByRequestID($message->getRequestID())) {
            $deferred = $call->getDeferred();
            $details  = $message->getDetails();

            if (empty($details['progress'])) {
                $deferred->resolve();
                $calls->remove($call);
            } else {
                $deferred->notify();
            }
        }
    }

    /**
     * @param Session      $session
     * @param ErrorMessage $message
     */
    private function processErrorMessage(Session $session, ErrorMessage $message)
    {
        switch ($message->getErrorMessageCode()) {
            case MessageCode::_CALL:
                $this->processErrorMessageFromCall($session, $message);
                break;
        }
    }

    /**
     * @param Session      $session
     * @param ErrorMessage $message
     */
    private function processErrorMessageFromCall(Session $session, ErrorMessage $message)
    {
        $calls = $session->callRequests ?: new CallCollection();

        if ($call = $calls->findByRequestID($message->getErrorRequestID())) {
            $deferred = $call->getDeferred();
            $deferred->reject();

            $calls->remove($call);
        }
    }
}