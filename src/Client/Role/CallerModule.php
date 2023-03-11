<?php

namespace PE\Component\WAMP\Client\Role;

use PE\Component\WAMP\Client\Client;
use PE\Component\WAMP\Client\ClientModuleInterface;
use PE\Component\WAMP\Client\SessionInterface;
use PE\Component\WAMP\Message\ErrorMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\ResultMessage;
use PE\Component\WAMP\Util\EventsInterface;

final class CallerModule implements ClientModuleInterface
{
    public function attach(EventsInterface $events): void
    {
        $events->attach(Client::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
        $events->attach(Client::EVENT_MESSAGE_SEND, [$this, 'onMessageSend']);
    }

    public function detach(EventsInterface $events): void
    {
        $events->detach(Client::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
        $events->detach(Client::EVENT_MESSAGE_SEND, [$this, 'onMessageSend']);
    }

    public function onMessageReceived(Message $message, SessionInterface $session): void
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

    private function processResultMessage(SessionInterface $session, ResultMessage $message): void
    {
        $session->callRequests = $session->callRequests ?: [];
        foreach ($session->callRequests as $key => $call) {
            if ($call->getRequestID() === $message->getRequestID()) {
                $deferred = $call->getDeferred();
                $details  = $message->getDetails();

                if (empty($details['progress'])) {
                    $deferred->resolve();
                    unset($session->callRequests[$key]);
                } else {
                    $deferred->notify();
                }
                break;
            }
        }
    }

    private function processErrorMessage(SessionInterface $session, ErrorMessage $message): void
    {
        if (Message::CODE_CALL === $message->getErrorMessageCode()) {
            $session->callRequests = $session->callRequests ?: [];
            foreach ($session->callRequests as $key => $call) {
                if ($call->getRequestID() === $message->getErrorRequestID()) {
                    $deferred = $call->getDeferred();
                    $deferred->reject();

                    unset($session->callRequests[$key]);
                    break;
                }
            }
        }
    }
}