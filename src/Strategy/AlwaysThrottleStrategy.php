<?php

namespace BenTools\FunnelHttpClient\Strategy;

final class AlwaysThrottleStrategy implements ThrottleStrategyInterface
{
    /**
     * @inheritDoc
     */
    public function shouldThrottle(string $method, string $url, array $options): bool
    {
        return true;
    }
}
