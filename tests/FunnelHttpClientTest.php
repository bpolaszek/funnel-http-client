<?php

namespace BenTools\FunnelHttpClient\Tests;

use BenTools\FunnelHttpClient\FunnelHttpClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class FunnelHttpClientTest extends TestCase
{

    /**
     * @test
     */
    public function it_throttles(): void
    {
        $mocked = new MockHttpClient(
            function () {
                return new MockResponse(\time());
            }
        );

        $client = FunnelHttpClient::throttle($mocked, $maxRequests = 2, $timeWindow = 3);
        $times = [];

        $start = \time();
        for ($i = 1; $i <= 10; $i++) {
            $time = (int) $client->request('GET', 'http://foo.bar')->getContent();
            if (!isset($times[$time])) {
                $times[$time] = 1;
            } else {
                $times[$time]++;
            }
        }

        $this->assertCount(5, $times);
        $this->assertEquals(2, \array_sum($times) / count($times));
        $this->assertGreaterThan(6, \time() - $start);
    }

    /**
     * @test
     */
    public function it_returns_a_valid_clone(): void
    {
        $mocked = new MockHttpClient(
            function () {
                return new MockResponse(\time());
            }
        );

        $a = FunnelHttpClient::throttle($mocked, $maxRequests = 10, $timeWindow = 1);
        $b = $a->withOptions(['foo' => 'bar']);
        $this->assertNotSame($a, $b);

        $refl = new \ReflectionProperty(FunnelHttpClient::class, 'decorated');
        $refl->setAccessible(true);
        $this->assertNotSame($refl->getValue($a), $refl->getValue($b));
    }

}
