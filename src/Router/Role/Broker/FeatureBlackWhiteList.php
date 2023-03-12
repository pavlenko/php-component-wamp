<?php

namespace PE\Component\WAMP\Router\Role\Broker;

use PE\Component\WAMP\Message\PublishMessage;
use PE\Component\WAMP\Router\Session\SessionInterface;
use PE\Component\WAMP\Router\DTO\Subscription;

final class FeatureBlackWhiteList implements BrokerFeatureInterface
{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'subscriber_blackwhite_listing';
    }

    public function processPublishMessage(SessionInterface $session, PublishMessage $message, Subscription $subscription): bool
    {
        if (is_array($message->getOption('exclude')) || is_array($message->getOption('eligible'))) {
            return $this->check(
                (string) $subscription->getSession()->getSessionID(),
                (array) $message->getOption('exclude'),
                (array) $message->getOption('eligible')
            );
        }

        if (is_array($message->getOption('exclude_authid')) || is_array($message->getOption('eligible_authid'))) {
            return $this->check(
                (string) $subscription->getSession()->authid,//TODO check
                (array) $message->getOption('exclude_authid'),
                (array) $message->getOption('eligible_authid')
            );
        }

        if (is_array($message->getOption('exclude_authrole')) || is_array($message->getOption('eligible_authrole'))) {
            return $this->check(
                (string) $subscription->getSession()->authrole,//TODO check
                (array) $message->getOption('exclude_authrole'),
                (array) $message->getOption('eligible_authrole')
            );
        }

        return true;
    }

    /**
     * @param string $identity
     * @param array $blackList
     * @param array $whiteList
     *
     * @return bool
     */
    private function check(string $identity, array $blackList, array $whiteList): bool
    {
        if (!empty($whiteList) && !in_array($identity, $whiteList, false)) {
            return false;
        }

        if (!empty($blackList) && in_array($identity, $whiteList, false)) {
            return false;
        }

        return true;
    }
}
