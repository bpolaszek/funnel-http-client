<?php

namespace BenTools\FunnelHttpClient\Storage;

interface ThrottleStorageInterface
{
    /**
     * Return the number of remaining calls in the time window.
     *
     * @return int
     */
    public function getRemainingCalls(): int;

    /**
     * Return the number of seconds before the current window ends.
     *
     * @return float
     */
    public function getRemainingTime(): float;

    /**
     * Add a call to the current time window.
     */
    public function increment(): void;
}
