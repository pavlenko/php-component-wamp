<?php

namespace PE\Component\WAMP\Router\Role;

use PE\Component\WAMP\ErrorURI;
use PE\Component\WAMP\Message\EventMessage;
use PE\Component\WAMP\Message\MessageFactory;
use PE\Component\WAMP\Message\PublishedMessage;
use PE\Component\WAMP\Message\PublishMessage;
use PE\Component\WAMP\Message\SubscribedMessage;
use PE\Component\WAMP\Message\SubscribeMessage;
use PE\Component\WAMP\Message\UnsubscribedMessage;
use PE\Component\WAMP\Message\UnsubscribeMessage;
use PE\Component\WAMP\Message\WelcomeMessage;
use PE\Component\WAMP\Router\Event\Events;
use PE\Component\WAMP\Router\Event\MessageEvent;
use PE\Component\WAMP\Router\Session;
use PE\Component\WAMP\Router\Subscription;
use PE\Component\WAMP\Util;

//TODO matchers: exact, prefix, wildcard
class Broker implements RoleInterface
{
    /**
     * @var Subscription[]
     */
    private $subscriptions = [];

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
            case ($message instanceof PublishMessage):
                $this->processPublishMessage($session, $message);
                break;
            case ($message instanceof SubscribeMessage):
                $this->processSubscribeMessage($session, $message);
                break;
            case ($message instanceof UnsubscribeMessage):
                $this->processUnsubscribeMessage($session, $message);
                break;
        }
    }

    /**
     * @param MessageEvent $event
     */
    public function onMessageSend(MessageEvent $event)
    {
        $message = $event->getMessage();

        if ($message instanceof WelcomeMessage) {
            $message->addFeatures('broker', [
                //TODO
            ]);
        }
    }

    /**
     * @param Session        $session
     * @param PublishMessage $message
     */
    private function processPublishMessage(Session $session, PublishMessage $message)
    {
        $publicationID = Util::generateID();

        foreach ($this->subscriptions as $subscriptionID => $subscription) {
            if ($subscription->match($message->getTopic())) {
                $subscription->getSession()->send(new EventMessage(
                    $subscriptionID,
                    $publicationID,
                    [],
                    $message->getArguments(),
                    $message->getArgumentsKw()
                ));
            }
        }

        if ($message->getOption('acknowledge')) {
            // If publisher require acknowledge - send PUBLISHED message to it
            $session->send(new PublishedMessage($message->getRequestID(), $publicationID));
        }
    }

    /**
     * @param Session          $session
     * @param SubscribeMessage $message
     */
    private function processSubscribeMessage(Session $session, SubscribeMessage $message)
    {
        $subscriptionID = Util::generateID();

        if ($message->getTopic()) {
            $this->subscriptions[$subscriptionID] = new Subscription(
                $session,
                $message->getTopic()
            );

            $session->send(new SubscribedMessage($message->getRequestID(), $subscriptionID));
        } else {
            $session->send(MessageFactory::createErrorMessageFromMessage($message, ErrorURI::_INVALID_URI));
        }
    }

    /**
     * @param Session            $session
     * @param UnsubscribeMessage $message
     */
    private function processUnsubscribeMessage(Session $session, UnsubscribeMessage $message)
    {
        if (isset($this->subscriptions[$message->getSubscriptionID()])) {
            $session->send(new UnsubscribedMessage($message->getRequestID()));
            unset($this->subscriptions[$message->getSubscriptionID()]);
        } else {
            $session->send(MessageFactory::createErrorMessageFromMessage($message, ErrorURI::_NO_SUCH_SUBSCRIPTION));
        }
    }
}