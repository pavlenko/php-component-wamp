<?php

namespace PE\Component\WAMP\Message;

/**
 * Event dispatched by Broker to Subscribers for subscriptions the event was matching.
 *
 * <code>[EVENT, SUBSCRIBED.Subscription|id, PUBLISHED.Publication|id, Details|dict]</code>
 * <code>[EVENT, SUBSCRIBED.Subscription|id, PUBLISHED.Publication|id, Details|dict, PUBLISH.Arguments|list]</code>
 * <code>[EVENT, SUBSCRIBED.Subscription|id, PUBLISHED.Publication|id, Details|dict, PUBLISH.Arguments|list, PUBLISH.ArgumentsKw|dict]</code>
 */
final class EventMessage extends Message
{
    use Details;
    use Arguments;

    /**
     * @var int
     */
    private int $subscriptionID;

    /**
     * @var int
     */
    private int $publicationID;

    /**
     * @param int $subscriptionID
     * @param int $publicationID
     * @param array      $details
     * @param array|null $arguments
     * @param array|null $argumentsKw
     */
    public function __construct(
        int   $subscriptionID,
        int   $publicationID,
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
    public function getCode(): int
    {
        return self::CODE_EVENT;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'EVENT';
    }

    /**
     * @inheritDoc
     */
    public function getParts(): array
    {
        return array_merge(
            [$this->getSubscriptionID(), $this->getPublicationID(), $this->getDetails()],
            $this->getArgumentsParts()
        );
    }

    /**
     * @return int
     */
    public function getSubscriptionID(): int
    {
        return $this->subscriptionID;
    }

    /**
     * @param int $subscriptionID
     *
     * @return self
     */
    public function setSubscriptionID(int $subscriptionID): EventMessage
    {
        $this->subscriptionID = $subscriptionID;
        return $this;
    }

    /**
     * @return int
     */
    public function getPublicationID(): int
    {
        return $this->publicationID;
    }

    /**
     * @param int $publicationID
     *
     * @return self
     */
    public function setPublicationID(int $publicationID): EventMessage
    {
        $this->publicationID = $publicationID;
        return $this;
    }
}
