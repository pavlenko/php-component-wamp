<?php

namespace PE\Component\WAMP\Message;

use PE\Component\WAMP\MessageCode;

/**
 * <code>[HEARTBEAT, IncomingSeq|integer, OutgoingSeq|integer]</code>
 * <code>[HEARTBEAT, IncomingSeq|integer, OutgoingSeq|integer, Discard|string]</code>
 */
class HeartbeatMessage extends Message
{
    /**
     * @var int
     */
    private $incomingSeq;

    /**
     * @var int
     */
    private $outgoingSeq;

    /**
     * @var string|null
     */
    private $discard;

    /**
     * @param int         $incomingSeq
     * @param int         $outgoingSeq
     * @param string|null $discard
     */
    public function __construct($incomingSeq, $outgoingSeq, $discard = null)
    {}

    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return MessageCode::_HEARTBEAT;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'HEARTBEAT';
    }

    /**
     * @inheritDoc
     */
    public function getParts()
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
    public function getIncomingSeq()
    {
        return $this->incomingSeq;
    }

    /**
     * @param int $incomingSeq
     *
     * @return self
     */
    public function setIncomingSeq($incomingSeq)
    {
        $this->incomingSeq = (int) $incomingSeq;
        return $this;
    }

    /**
     * @return int
     */
    public function getOutgoingSeq()
    {
        return $this->outgoingSeq;
    }

    /**
     * @param int $outgoingSeq
     *
     * @return self
     */
    public function setOutgoingSeq($outgoingSeq)
    {
        $this->outgoingSeq = (int) $outgoingSeq;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getDiscard()
    {
        return $this->discard;
    }

    /**
     * @param null|string $discard
     *
     * @return self
     */
    public function setDiscard($discard)
    {
        $this->discard = $discard ? (string) $discard : null;
        return $this;
    }
}