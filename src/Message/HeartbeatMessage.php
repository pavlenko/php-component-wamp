<?php

namespace PE\Component\WAMP\Message;

/**
 * <code>[HEARTBEAT, IncomingSeq|integer, OutgoingSeq|integer]</code>
 * <code>[HEARTBEAT, IncomingSeq|integer, OutgoingSeq|integer, Discard|string]</code>
 *
 * @codeCoverageIgnore
 */
final class HeartbeatMessage extends Message
{
    /**
     * @var int
     */
    private int $incomingSeq;

    /**
     * @var int
     */
    private int $outgoingSeq;

    /**
     * @var string|null
     */
    private ?string $discard;

    /**
     * @param int $incomingSeq
     * @param int $outgoingSeq
     * @param string|null $discard
     */
    public function __construct(int $incomingSeq, int $outgoingSeq, string $discard = null)
    {
        //TODO
    }

    /**
     * @inheritDoc
     */
    public function getCode(): int
    {
        return self::CODE_HEARTBEAT;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'HEARTBEAT';
    }

    /**
     * @inheritDoc
     */
    public function getParts(): array
    {
        $parts = [$this->getIncomingSeq(), $this->getOutgoingSeq()];

        if ($discard = $this->getDiscard()) {
            $parts[] = $discard;
        }

        return $parts;
    }

    /**
     * @return int
     */
    public function getIncomingSeq(): int
    {
        return $this->incomingSeq;
    }

    /**
     * @param int $incomingSeq
     *
     * @return self
     */
    public function setIncomingSeq(int $incomingSeq): HeartbeatMessage
    {
        $this->incomingSeq = $incomingSeq;
        return $this;
    }

    /**
     * @return int
     */
    public function getOutgoingSeq(): int
    {
        return $this->outgoingSeq;
    }

    /**
     * @param int $outgoingSeq
     *
     * @return self
     */
    public function setOutgoingSeq(int $outgoingSeq): HeartbeatMessage
    {
        $this->outgoingSeq = $outgoingSeq;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getDiscard(): ?string
    {
        return $this->discard;
    }

    /**
     * @param string|null $discard
     *
     * @return self
     */
    public function setDiscard(?string $discard): HeartbeatMessage
    {
        $this->discard = $discard ? $discard : null;
        return $this;
    }
}
