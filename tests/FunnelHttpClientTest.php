<?php

namespace BenTools\FunnelHttpClient\Tests;

use BenTools\FunnelHttpClient\FunnelHttpClient;
use BenTools\FunnelHttpClient\Storage\ArrayStorage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class FunnelHttpClientTest extends TestCase
{

    /**
     * @test
     */
    function it_throttles()
    {
        $mocked = new MockHttpClient(
            function () {
                return new MockResponse(\time());
            }
        );

        $storage = new ArrayStorage($maxRequests = 2, $timeWindow = 3);
        $client = new FunnelHttpClient($mocked, $storage);
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

}
