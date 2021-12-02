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

final class CallerModule implements ClientModuleInterface
{
    public function subscribe(Client $client): void
    {
        $client->on(Client::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
        $client->on(Client::EVENT_MESSAGE_SEND, [$this, 'onMessageSend']);
    }

    public function unsubscribe(Client $client): void
    {
        $client->off(Client::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
        $client->off(Client::EVENT_MESSAGE_SEND, [$this, 'onMessageSend']);
    }

    public function onMessageReceived(Message $message, Session $session): void
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

    public function onMessageSend(Message $message): void
    {
        if ($message instanceof HelloMessage) {
            $message->addFeatures('caller', [
                //TODO
            ]);
        }
    }

    private function processResultMessage(Session $session, ResultMessage $message): void
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

    private function processErrorMessage(Session $session, ErrorMessage $message): void
    {
        switch ($message->getErrorMessageCode()) {
            case MessageCode::_CALL:
                $this->processErrorMessageFromCall($session, $message);
                break;
        }
    }

    private function processErrorMessageFromCall(Session $session, ErrorMessage $message): void
    {
        $calls = $session->callRequests ?: new CallCollection();

        if ($call = $calls->findByRequestID($message->getErrorRequestID())) {
            $deferred = $call->getDeferred();
            $deferred->reject();

            $calls->remove($call);
        }
    }
}