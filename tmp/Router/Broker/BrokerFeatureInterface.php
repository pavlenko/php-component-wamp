<?php

namespace Broker;

use PE\Component\WAMP\Message\PublishMessage;
use PE\Component\WAMP\Router\DTO\Subscription;
use PE\Component\WAMP\Router\Session\SessionInterface;

/**
 * @deprecated
 */
interface BrokerFeatureInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param SessionInterface $session
     * @param PublishMessage $message
     * @param Subscription $subscription
     *
     * @return bool
     */
    public function processPublishMessage(SessionInterface $session, PublishMessage $message, Subscription $subscription): bool;
}
