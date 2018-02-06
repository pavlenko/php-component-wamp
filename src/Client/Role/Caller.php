<?php

namespace PE\Component\WAMP\Client\Role;

use PE\Component\WAMP\Client\Event\Events;
use PE\Component\WAMP\Client\Event\MessageEvent;
use PE\Component\WAMP\Client\Session;
use PE\Component\WAMP\Message\CallMessage;
use PE\Component\WAMP\Message\CancelMessage;
use PE\Component\WAMP\Message\ErrorMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\ResultMessage;
use PE\Component\WAMP\MessageCode;
use PE\Component\WAMP\Util;

class Caller implements RoleInterface
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
            case ($message instanceof ResultMessage):
                $this->processResultMessage($message);
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
     * @param Session    $session
     * @param string     $procedureURI
     * @param array|null $arguments
     * @param array|null $argumentsKw
     * @param array|null $options
     */
    public function call(Session $session, $procedureURI, array $arguments = null, array $argumentsKw = null, array $options = null)
    {
        if (!in_array($procedureURI, $this->requests, false)) {
            $requestID = Util::generateID();

            $this->requests[$requestID] = $procedureURI;

            $session->send(new CallMessage($requestID, $options ?: [], $procedureURI, $arguments, $argumentsKw));
        }
    }

    /**
     * @param Session    $session
     * @param string     $procedureIRI
     * @param array|null $options
     */
    public function cancel(Session $session, $procedureIRI, array $options = null)
    {
        $requestID = array_search($procedureIRI, $this->requests, false);

        if (false === $requestID) {
            $session->send(new CancelMessage($requestID, $options ?: []));
        }
    }

    /**
     * @param ResultMessage $message
     */
    private function processResultMessage(ResultMessage $message)
    {
        if (isset($this->requests[$message->getRequestID()])) {
            $details = $message->getDetails();

            if (empty($details['progress'])) {
                unset($this->requests[$message->getRequestID()]);
            }
        }
    }

    /**
     * @param ErrorMessage $message
     */
    private function processErrorMessage(ErrorMessage $message)
    {
        switch ($message->getErrorMessageCode()) {
            case MessageCode::_CALL:
                $this->processErrorMessageFromCall($message);
                break;
            case MessageCode::_CANCEL:
                $this->processErrorMessageFromCancel($message);
                break;
        }
    }

    /**
     * @param ErrorMessage $message
     */
    private function processErrorMessageFromCall(ErrorMessage $message)
    {
        if (isset($this->requests[$message->getErrorRequestID()])) {
            unset($this->requests[$message->getErrorRequestID()]);
        }
    }

    /**
     * @param ErrorMessage $message
     */
    private function processErrorMessageFromCancel(ErrorMessage $message)
    {
        if (isset($this->requests[$message->getErrorRequestID()])) {
            unset($this->requests[$message->getErrorRequestID()]);
        }
    }
}