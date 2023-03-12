<?php

namespace Broker;

use PE\Component\WAMP\Message\PublishMessage;
use PE\Component\WAMP\Router\DTO\Subscription;
use PE\Component\WAMP\Router\Session\SessionInterface;

final class FeaturePublisherExclusion implements BrokerFeatureInterface
{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'publisher_exclusion';
    }

    /**
     * @inheritDoc
     */
    public function processPublishMessage(SessionInterface $session, PublishMessage $message, Subscription $subscription): bool
    {
        return false === (bool) $message->getOption('exclude_me')
            && $session->getSessionID() === $subscription->getSession()->getSessionID();
    }
}
