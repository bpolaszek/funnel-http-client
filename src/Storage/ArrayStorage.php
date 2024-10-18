<?php

namespace BenTools\FunnelHttpClient\Storage;

final class ArrayStorage implements ThrottleStorageInterface
{
    private int $currentRequests = 0;

    private float|null $startedAt = null;

    public function __construct(
        private int $maxRequests,
        private float $timeWindow
    ) {
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

    public function decrement(): void
    {
        $this->currentRequests--;
        if ($this->currentRequests <= 0) {
            $this->reset();
        }
    }


    private function reset(): void
    {
        $this->currentRequests = 0;
        $this->startedAt = null;
    }

    private function isExpired(): bool
    {
        if (null === $this->startedAt) {
            return false;
        }

        return \microtime(true) > ($this->startedAt + $this->timeWindow);
    }
}
