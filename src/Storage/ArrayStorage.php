<?php

namespace BenTools\FunnelHttpClient\Storage;

final class ArrayStorage implements ThrottleStorageInterface
{
    /**
     * @var int
     */
    private $maxRequests;

    /**
     * @var float
     */
    private $timeWindow;

    /**
     * @var int
     */
    private $currentRequests = 0;

    /**
     * @var float|null
     */
    private $startedAt;

    /**
     * ArrayStorage constructor.
     */
    public function __construct(int $maxRequests, float $timeWindow)
    {
        $this->maxRequests = $maxRequests;
        $this->timeWindow = $timeWindow;
    }

    /**
     * @inheritDoc
     */
    public function getRemainingCalls(): int
    {
        return \max(0, ($this->maxRequests - $this->currentRequests));
    }

    /**
     * @inheritDoc
     */
    public function getRemainingTime(): float
    {
        if (null === $this->startedAt) {
            return 0;
        }

        return \max(0, ($this->startedAt + $this->timeWindow) - \microtime(true));
    }

    /**
     * @inheritDoc
     */
    public function increment(): void
    {
        if ($this->isExpired()) {
            $this->reset();
        }

        if (null === $this->startedAt) {
            $this->startedAt = \microtime(true);
        }

        $this->currentRequests++;
    }

    /**
     *
     */
    private function reset()
    {
        $this->currentRequests = 0;
        $this->startedAt = null;
    }

    /**
     * @return bool
     */
    private function isExpired(): bool
    {
        if (null === $this->startedAt) {
            return false;
        }

        return \microtime(true) > ($this->startedAt + $this->timeWindow);
    }
}
