<?php

namespace PE\Component\WAMP\Router\Role\Broker;

use PE\Component\WAMP\Message\PublishMessage;
use PE\Component\WAMP\Router\Session;
use PE\Component\WAMP\Router\Subscription;

interface BrokerFeatureInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param Session        $session
     * @param PublishMessage $message
     * @param Subscription   $subscription
     *
     * @return bool
     */
    public function processPublishMessage(Session $session, PublishMessage $message, Subscription $subscription);
}
