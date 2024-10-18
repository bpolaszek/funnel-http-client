<?php

namespace BenTools\FunnelHttpClient;

use BenTools\FunnelHttpClient\Storage\ArrayStorage;
use BenTools\FunnelHttpClient\Storage\ThrottleStorageInterface;
use BenTools\FunnelHttpClient\Strategy\AlwaysThrottleStrategy;
use BenTools\FunnelHttpClient\Strategy\ThrottleStrategyInterface;
use LogicException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

use function method_exists;

final class FunnelHttpClient implements HttpClientInterface
{
    public function __construct(
        private HttpClientInterface $decorated,
        private ThrottleStorageInterface $throttleStorage,
        private ?ThrottleStrategyInterface $throttleStrategy = null,
        private ?LoggerInterface $logger = null
    ) {
        $this->throttleStrategy = $throttleStrategy ?? new AlwaysThrottleStrategy();
        $this->logger = $logger ?? new NullLogger();
    }

    public function withOptions(array $options): static
    {
        // SF 5+ compatibility
        if (!method_exists($this->decorated, 'withOptions')) {
            throw new LogicException('%s doesn\'t implement `withOptions`.');
        }

        $clone = clone $this;
        $clone->decorated = $clone->decorated->withOptions($options);

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        if (!$this->throttleStrategy->shouldThrottle($method, $url, $options)) {
            return $this->decorated->request($method, $url, $options);
        }

        if (0 === $this->throttleStorage->getRemainingCalls()) {
            $this->waitUntilReady($method, $url);
        }

        $this->throttleStorage->increment();
        try {
            $response = $this->decorated->request($method, $url, $options);
        } catch (TransportExceptionInterface $e) {
            $this->throttleStorage->decrement();
            throw $e;
        }

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function stream($responses, float $timeout = null): ResponseStreamInterface
    {
        return $this->decorated->stream($responses, $timeout);
    }

    /**
     * @param string $method
     * @param string $url
     */
    private function waitUntilReady(string $method, string $url): void
    {
        $remainingSeconds = $this->throttleStorage->getRemainingTime();
        $this->logger->info(\sprintf('Max requests / window reached. Waiting %s seconds...', $remainingSeconds), ['method' => $method, 'url' => $url]);

        if (0 === ($remainingSeconds <=> (int) $remainingSeconds)) {
            \sleep((int) $remainingSeconds);
        } else {
            \usleep((int) \round($remainingSeconds * 1000000));
        }
    }

    /**
     * @param HttpClientInterface  $client
     * @param int                  $maxRequests
     * @param float                $timeWindow
     * @param LoggerInterface|null $logger
     * @return FunnelHttpClient
     */
    public static function throttle(HttpClientInterface $client, int $maxRequests, float $timeWindow, ?LoggerInterface $logger = null): self
    {
        return new self($client, new ArrayStorage($maxRequests, $timeWindow), null, $logger);
    }
}
