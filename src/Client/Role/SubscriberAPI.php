<?php

namespace PE\Component\WAMP\Client\Role;

use PE\Component\WAMP\Client\Session\SessionInterface;
use PE\Component\WAMP\Client\DTO\Subscription;
use PE\Component\WAMP\Message\SubscribeMessage;
use PE\Component\WAMP\Message\UnsubscribeMessage;
use PE\Component\WAMP\Util;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use function React\Promise\reject;

final class SubscriberAPI
{
    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function subscribe(string $topic, \Closure $callback, array $options = []): PromiseInterface
    {
        $requestID = Util::generateID();

        $subscription = new Subscription($topic, $callback);
        $subscription->setSubscribeRequestID($requestID);
        $subscription->setSubscribeDeferred(new Deferred());

        $this->session->subscriptions = array_merge($this->session->subscriptions ?: [], [$subscription]);
        $this->session->send(new SubscribeMessage($requestID, $options, $topic));

        return $subscription->getSubscribeDeferred()->promise();
    }

    public function unsubscribe(string $topic, \Closure $callback): PromiseInterface
    {
        $subscriptions = $this->session->subscriptions ?: [];
        foreach ($subscriptions as $subscription) {
            if ($subscription->getTopic() === $topic && $subscription->getCallback() === $callback) {
                $requestID = Util::generateID();
                $subscription->getSubscribeDeferred()->reject();
                $subscription->setUnsubscribeRequestID($requestID);
                $subscription->setUnsubscribeDeferred(new Deferred());

                $this->session->send(new UnsubscribeMessage($requestID, $subscription->getSubscriptionID()));

                return $subscription->getUnsubscribeDeferred()->promise();
            }
        }

        return reject();
    }
}