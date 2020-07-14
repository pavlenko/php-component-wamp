<?php

namespace PE\Component\WAMP\Router\Role\Broker;

use PE\Component\WAMP\ErrorURI;
use PE\Component\WAMP\Message\EventMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\MessageFactory;
use PE\Component\WAMP\Message\PublishedMessage;
use PE\Component\WAMP\Message\PublishMessage;
use PE\Component\WAMP\Message\SubscribedMessage;
use PE\Component\WAMP\Message\SubscribeMessage;
use PE\Component\WAMP\Message\UnsubscribedMessage;
use PE\Component\WAMP\Message\UnsubscribeMessage;
use PE\Component\WAMP\Message\WelcomeMessage;
use PE\Component\WAMP\Router\Router;
use PE\Component\WAMP\Router\RouterModuleInterface;
use PE\Component\WAMP\Router\Session;
use PE\Component\WAMP\Router\Subscription;
use PE\Component\WAMP\Util;

class BrokerModule implements RouterModuleInterface
{
    /**
     * @var BrokerFeatureInterface[]
     */
    private $features = [];

    /**
     * @var Subscription[]
     */
    private $subscriptions = [];

    /**
     * @param BrokerFeatureInterface $feature
     */
    public function addFeature(BrokerFeatureInterface $feature)
    {
        $this->features[get_class($feature)] = $feature;
    }

    /**
     * @inheritDoc
     */
    public function subscribe(Router $router)
    {
        $router->on(Router::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
        $router->on(Router::EVENT_MESSAGE_SEND, [$this, 'onMessageSend']);
    }

    /**
     * @inheritDoc
     */
    public function unsubscribe(Router $router)
    {
        $router->off(Router::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
        $router->off(Router::EVENT_MESSAGE_SEND, [$this, 'onMessageSend']);
    }

    /**
     * @param Message $message
     * @param Session $session
     */
    public function onMessageReceived(Message $message, Session $session)
    {
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
     * @param Message $message
     * @param Session $session
     */
    public function onMessageSend(Message $message, Session $session)
    {
        if ($message instanceof WelcomeMessage) {
            $features = [];
            foreach ($this->features as $feature) {
                $features[$feature->getName()] = true;
            }

            $message->addFeatures('broker', $features);
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
            if ($subscription->getTopic() === $message->getTopic()) {
                foreach ($this->features as $feature) {
                    if (!$feature->processPublishMessage($session, $message, $subscription)) {
                        continue;
                    }
                }

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
