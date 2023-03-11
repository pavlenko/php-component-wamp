<?php

namespace PE\Component\WAMP\Router\Role;

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
use PE\Component\WAMP\Router\Role\Broker\BrokerFeatureInterface;
use PE\Component\WAMP\Router\Subscription;
use PE\Component\WAMP\Router\Router;
use PE\Component\WAMP\Router\RouterModuleInterface;
use PE\Component\WAMP\Router\SessionInterface;
use PE\Component\WAMP\Util;
use PE\Component\WAMP\Util\EventsInterface;

final class BrokerModule implements RouterModuleInterface
{
    /**
     * @var BrokerFeatureInterface[]
     */
    private array $features = [];

    /**
     * @var Subscription[]
     */
    private array $subscriptions = [];

    public function addFeature(BrokerFeatureInterface $feature): void
    {
        $this->features[get_class($feature)] = $feature;
    }

    public function attach(EventsInterface $events): void
    {
        $events->attach(Router::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
        $events->attach(Router::EVENT_MESSAGE_SEND, [$this, 'onMessageSend']);
        $events->attach(Router::EVENT_CONNECTION_CLOSE, [$this, 'onConnectionClose']);
    }

    public function detach(EventsInterface $events): void
    {
        $events->detach(Router::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
        $events->detach(Router::EVENT_MESSAGE_SEND, [$this, 'onMessageSend']);
        $events->detach(Router::EVENT_CONNECTION_CLOSE, [$this, 'onConnectionClose']);
    }

    public function onMessageReceived(Message $message, SessionInterface $session): void
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

    public function onMessageSend(Message $message): void
    {
        if ($message instanceof WelcomeMessage) {
            $features = [
                'payload_passthru_mode'      => false,//TODO
                'publisher_identification'   => false,//TODO
                'publication_trustlevels'    => false,//TODO
                'pattern_based_subscription' => false,//TODO
            ];
            foreach ($this->features as $feature) {
                $features[$feature->getName()] = true;
            }

            $message->addFeatures('broker', $features);
        }
    }

    public function onConnectionClose(): void
    {
        $this->subscriptions = [];
    }

    private function processPublishMessage(SessionInterface $session, PublishMessage $message): void
    {
        $publicationID = Util::generateID();

        foreach ($this->subscriptions as $subscriptionID => $subscription) {
            if ($session !== $subscription->getSession() && $subscription->getTopic() === $message->getTopic()) {
                foreach ($this->features as $feature) {
                    if (!$feature->processPublishMessage($session, $message, $subscription)) {
                        //TODO what is do here???
                        break;
                    }
                }

                // If publisher_identification feature supported and $message->getOption('disclose_me') === true
                //   you can send $details['publisher'] = <publisher_session_id>
                $subscription->getSession()->send(new EventMessage(
                    $subscriptionID,
                    $publicationID,
                    [$message->getArguments()[0] ?? null],
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

    private function processSubscribeMessage(SessionInterface $session, SubscribeMessage $message): void
    {
        $subscriptionID = Util::generateID();

        if ($message->getTopic()) {
            $this->subscriptions[$subscriptionID] = new Subscription($session, $message->getTopic());
            $session->send(new SubscribedMessage($message->getRequestID(), $subscriptionID));
        } else {
            $session->send(MessageFactory::createErrorMessageFromMessage($message, Message::ERROR_INVALID_URI));
        }
    }

    private function processUnsubscribeMessage(SessionInterface $session, UnsubscribeMessage $message): void
    {
        if (isset($this->subscriptions[$message->getSubscriptionID()])) {
            $session->send(new UnsubscribedMessage($message->getRequestID()));
            unset($this->subscriptions[$message->getSubscriptionID()]);
        } else {
            $session->send(MessageFactory::createErrorMessageFromMessage($message, Message::ERROR_NO_SUCH_SUBSCRIPTION));
        }
    }
}
