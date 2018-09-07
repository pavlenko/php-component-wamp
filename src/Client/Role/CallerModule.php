<?php

namespace PE\Component\WAMP\Client\Role;

use PE\Component\WAMP\Client\CallCollection;
use PE\Component\WAMP\Client\Client;
use PE\Component\WAMP\Client\ClientModuleInterface;
use PE\Component\WAMP\Client\Session;
use PE\Component\WAMP\Message\ErrorMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\ResultMessage;
use PE\Component\WAMP\MessageCode;

class CallerModule implements ClientModuleInterface
{
    /**
     * @inheritDoc
     */
    public function subscribe(Client $client)
    {
        $client->on(Client::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
        $client->on(Client::EVENT_MESSAGE_SEND, [$this, 'onMessageSend']);
    }

    /**
     * @inheritDoc
     */
    public function unsubscribe(Client $client)
    {
        $client->off(Client::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
        $client->off(Client::EVENT_MESSAGE_SEND, [$this, 'onMessageSend']);
    }

    /**
     * @param Message $message
     * @param Session $session
     */
    public function onMessageReceived(Message $message, Session $session)
    {
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
     * @param Message $message
     */
    public function onMessageSend(Message $message)
    {
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