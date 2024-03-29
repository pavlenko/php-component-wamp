<?php

namespace PE\Component\WAMP\Client\Role;

use PE\Component\WAMP\Client\ClientInterface;
use PE\Component\WAMP\Client\ClientModuleInterface;
use PE\Component\WAMP\Client\Session\SessionInterface;
use PE\Component\WAMP\Message\ErrorMessage;
use PE\Component\WAMP\Message\EventMessage;
use PE\Component\WAMP\Message\HelloMessage;
use PE\Component\WAMP\Message\Message;
use PE\Component\WAMP\Message\SubscribedMessage;
use PE\Component\WAMP\Message\UnsubscribedMessage;
use PE\Component\WAMP\Util\EventsInterface;

final class SubscriberModule implements ClientModuleInterface
{
    /**
     * @var SubscriberFeatureInterface[]
     */
    private array $features;

    public function __construct(SubscriberFeatureInterface ...$features)
    {
        $this->features = $features;
    }

    public function attach(EventsInterface $events): void
    {
        $events->attach(ClientInterface::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
        $events->attach(ClientInterface::EVENT_MESSAGE_SEND, [$this, 'onMessageSend']);
    }

    public function detach(EventsInterface $events): void
    {
        $events->detach(ClientInterface::EVENT_MESSAGE_RECEIVED, [$this, 'onMessageReceived']);
        $events->detach(ClientInterface::EVENT_MESSAGE_SEND, [$this, 'onMessageSend']);
    }

    public function onMessageReceived(Message $message, SessionInterface $session): void
    {
        switch (true) {
            case ($message instanceof SubscribedMessage):
                $this->processSubscribedMessage($session, $message);
                break;
            case ($message instanceof UnsubscribedMessage):
                $this->processUnsubscribedMessage($session, $message);
                break;
            case ($message instanceof EventMessage):
                $this->processEventMessage($session, $message);
                break;
            case ($message instanceof ErrorMessage):
                $this->processErrorMessage($session, $message);
                break;
        }
    }

    public function onMessageSend(Message $message): void
    {
        if ($message instanceof HelloMessage) {
            // Possible features, by default disabled
            $message->setFeatures('subscriber', [
                'payload_passthru_mode'      => false,
                'publisher_identification'   => false,
                'publication_trustlevels'    => false,
                'pattern_based_subscription' => false,
            ]);
            foreach ($this->features as $feature) {
                $message->setFeature('subscriber', $feature->getName());
            }
        }
    }

    private function processSubscribedMessage(SessionInterface $session, SubscribedMessage $message): void
    {
        $session->subscriptions = $session->subscriptions ?: [];
        foreach ($session->subscriptions as $subscription) {
            if ($subscription->getSubscribeRequestID() === $message->getRequestID()) {
                $subscription->setSubscriptionID($message->getSubscriptionID());
                $subscription->getSubscribeDeferred()->resolve();
            }
        }
    }

    private function processUnsubscribedMessage(SessionInterface $session, UnsubscribedMessage $message): void
    {
        $subscriptions = $session->subscriptions ?: [];
        foreach ($subscriptions as $key => $subscription) {
            if ($subscription->getUnsubscribeRequestID() === $message->getRequestID()) {
                $subscription->getUnsubscribeDeferred()->resolve();
                unset($subscriptions[$key]);
            }
        }
        $session->subscriptions = $subscriptions;
    }

    private function processEventMessage(SessionInterface $session, EventMessage $message): void
    {
        $subscriptions = $session->subscriptions ?: [];
        foreach ($subscriptions as $subscription) {
            if ($subscription->getSubscriptionID() === $message->getSubscriptionID()) {
                call_user_func(
                    $subscription->getCallback(),
                    $message->getArguments(),
                    $message->getArgumentsKw(),
                    $message->getDetails(),
                    $message->getPublicationID()
                );
            }
        }
    }

    private function processErrorMessage(SessionInterface $session, ErrorMessage $message): void
    {
        switch ($message->getErrorMessageCode()) {
            case Message::CODE_SUBSCRIBE:
                $this->processErrorMessageFromSubscribe($session, $message);
                break;
            case Message::CODE_UNSUBSCRIBE:
                $this->processErrorMessageFromUnsubscribe($session, $message);
                break;
        }
    }

    private function processErrorMessageFromSubscribe(SessionInterface $session, ErrorMessage $message): void
    {
        $subscriptions = $session->subscriptions ?: [];
        foreach ($subscriptions as $key => $subscription) {
            if ($subscription->getSubscribeRequestID() === $message->getErrorRequestID()) {
                $subscription->getSubscribeDeferred()->reject();
                unset($subscriptions[$key]);
            }
        }
        $session->subscriptions = $subscriptions;
    }

    private function processErrorMessageFromUnsubscribe(SessionInterface $session, ErrorMessage $message): void
    {
        $subscriptions = $session->subscriptions ?: [];
        foreach ($subscriptions as  $key =>$subscription) {
            if ($subscription->getUnsubscribeRequestID() === $message->getErrorRequestID()) {
                $subscription->getUnsubscribeDeferred()->reject();
                unset($subscriptions[$key]);
            }
        }
        $session->subscriptions = $subscriptions;
    }
}