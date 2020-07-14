<?php

namespace PE\Component\WAMP\Router\Role\Broker;

use PE\Component\WAMP\Message\PublishMessage;
use PE\Component\WAMP\Router\Session;
use PE\Component\WAMP\Router\Subscription;

final class FeaturePublisherExclusion implements BrokerFeatureInterface
{
    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'publisher_exclusion';
    }

    /**
     * @inheritDoc
     */
    public function processPublishMessage(Session $session, PublishMessage $message, Subscription $subscription)
    {
        return false === (bool) $message->getOption('exclude_me')
            && $session->getSessionID() === $subscription->getSession()->getSessionID();
    }
}
