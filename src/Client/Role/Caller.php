<?php

namespace PE\Component\WAMP\Client\Role;

use PE\Component\WAMP\Client\Call;
use PE\Component\WAMP\Client\CallCollection;
use PE\Component\WAMP\Client\Event\Events;
use PE\Component\WAMP\Client\Event\MessageEvent;
use PE\Component\WAMP\Message\CallMessage;
use PE\Component\WAMP\Message\CancelMessage;
use PE\Component\WAMP\Message\ErrorMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\ResultMessage;
use PE\Component\WAMP\MessageCode;
use PE\Component\WAMP\Session;
use PE\Component\WAMP\Util;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;

class Caller implements RoleInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
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
     * @param string $procedureURI
     * @param array  $arguments
     * @param array  $argumentsKw
     * @param array  $options
     *
     * @return PromiseInterface
     */
    public function call($procedureURI, array $arguments = [], array $argumentsKw = [], array $options = [])
    {
        $requestID = Util::generateID();

        $deferred = new Deferred(function () use ($requestID) {
            // This is only one possible point to cancel a call
            $this->session->send(new CancelMessage($requestID, []));
        });

        if (!($this->session->callRequests instanceof CallCollection)) {
            $this->session->callRequests = new CallCollection();
        }

        $this->session->callRequests->add(new Call($requestID, $deferred));

        $this->session->send(new CallMessage($requestID, $options ?: [], $procedureURI, $arguments, $argumentsKw));

        return $deferred->promise();
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