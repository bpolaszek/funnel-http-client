<?php

namespace BenTools\FunnelHttpClient;

use BenTools\FunnelHttpClient\Storage\ThrottleStorageInterface;
use BenTools\FunnelHttpClient\Strategy\AlwaysThrottleStrategy;
use BenTools\FunnelHttpClient\Strategy\ThrottleStrategyInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

final class FunnelHttpClient implements HttpClientInterface
{
    /**
     * @var HttpClientInterface
     */
    private $decorated;

    /**
     * @var ThrottleStorageInterface
     */
    private $throttleStorage;

    /**
     * @var ThrottleStrategyInterface|null
     */
    private $throttleStrategy;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * FunnelHttpClient constructor.
     *
     * @param HttpClientInterface            $decorated
     * @param ThrottleStorageInterface       $throttleStorage
     * @param ThrottleStrategyInterface|null $throttleStrategy
     * @param LoggerInterface|null           $logger
     */
    public function __construct(
        HttpClientInterface $decorated,
        ThrottleStorageInterface $throttleStorage,
        ?ThrottleStrategyInterface $throttleStrategy = null,
        ?LoggerInterface $logger = null
    ) {
        $this->decorated = $decorated;
        $this->throttleStorage = $throttleStorage;
        $this->throttleStrategy = $throttleStrategy ?? new AlwaysThrottleStrategy();
        $this->logger = $logger ?? new NullLogger();
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
            $this->throttle($method, $url);
        }

        $response = $this->decorated->request($method, $url, $options);
        $this->throttleStorage->increment();
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
    private function throttle(string $method, string $url): void
    {
        $remainingSeconds = $this->throttleStorage->getRemainingTime();
        $this->logger->info(\sprintf('Max requests / window reached. Waiting %s seconds...', $remainingSeconds), ['method' => $method, 'url' => $url]);

        if (0 === ($remainingSeconds <=> (int) $remainingSeconds)) {
            \sleep((int) $remainingSeconds);
        } else {
            \usleep((int) \round($remainingSeconds * 1000000));
        }
    }
}
