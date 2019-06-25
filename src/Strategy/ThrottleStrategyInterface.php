<?php

namespace BenTools\FunnelHttpClient\Strategy;

interface ThrottleStrategyInterface
{

    /**
     * Return wether or not this request should be throttled.
     *
     * @param string $method
     * @param string $url
     * @param array  $options
     * @return bool
     */
    public function shouldThrottle(string $method, string $url, array $options): bool;
}
