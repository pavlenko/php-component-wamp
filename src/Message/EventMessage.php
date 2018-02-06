<?php

namespace PE\Component\WAMP\Message;

use PE\Component\WAMP\MessageCode;

/**
 * Event dispatched by Broker to Subscribers for subscriptions the event was matching.
 *
 * <code>[EVENT, SUBSCRIBED.Subscription|id, PUBLISHED.Publication|id, Details|dict]</code>
 * <code>[EVENT, SUBSCRIBED.Subscription|id, PUBLISHED.Publication|id, Details|dict, PUBLISH.Arguments|list]</code>
 * <code>[EVENT, SUBSCRIBED.Subscription|id, PUBLISHED.Publication|id, Details|dict, PUBLISH.Arguments|list, PUBLISH.ArgumentsKw|dict]</code>
 */
class EventMessage extends Message
{
    use Details;
    use Arguments;

    /**
     * @var int
     */
    private $subscriptionID;

    /**
     * @var int
     */
    private $publicationID;

    /**
     * @param int        $subscriptionID
     * @param int        $publicationID
     * @param array      $details
     * @param array|null $arguments
     * @param array|null $argumentsKw
     */
    public function __construct(
        $subscriptionID,
        $publicationID,
        array $details,
        array $arguments = null,
        array $argumentsKw = null
    ) {
        $this->setSubscriptionID($subscriptionID);
        $this->setPublicationID($publicationID);
        $this->setDetails($details);
        $this->setArguments($arguments);
        $this->setArgumentsKw($argumentsKw);
    }

    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return MessageCode::_EVENT;
    }

    /**
     * @inheritDoc
     */
    public function getParts()
    {
        return array_merge(
            [$this->getSubscriptionID(), $this->getPublicationID(), $this->getDetails()],
            $this->getArgumentsParts()
        );
    }

    /**
     * @return int
     */
    public function getSubscriptionID()
    {
        return $this->subscriptionID;
    }

    /**
     * @param int $subscriptionID
     *
     * @return self
     */
    public function setSubscriptionID($subscriptionID)
    {
        $this->subscriptionID = (int) $subscriptionID;
        return $this;
    }

    /**
     * @return int
     */
    public function getPublicationID()
    {
        return $this->publicationID;
    }

    /**
     * @param int $publicationID
     *
     * @return self
     */
    public function setPublicationID($publicationID)
    {
        $this->publicationID = (int) $publicationID;
        return $this;
    }
}