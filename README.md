[![Latest Stable Version](https://poser.pugx.org/bentools/funnel-http-client/v/stable)](https://packagist.org/packages/bentools/funnel-http-client)
[![License](https://poser.pugx.org/bentools/funnel-http-client/license)](https://packagist.org/packages/bentools/funnel-http-client)
[![CI Workflow](https://github.com/bpolaszek/funnel-http-client/actions/workflows/ci-workflow.yml/badge.svg)](https://github.com/bpolaszek/funnel-http-client/actions/workflows/ci-workflow.yml)
[![Coverage Status](https://coveralls.io/repos/github/bpolaszek/funnel-http-client/badge.svg?branch=master)](https://coveralls.io/github/bpolaszek/funnel-http-client?branch=master)
[![Quality Score](https://img.shields.io/scrutinizer/g/bpolaszek/funnel-http-client.svg?style=flat-square)](https://scrutinizer-ci.com/g/bpolaszek/funnel-http-client)
[![Total Downloads](https://poser.pugx.org/bentools/funnel-http-client/downloads)](https://packagist.org/packages/bentools/funnel-http-client)

# :vertical_traffic_light: Funnel Http Client

A decorator for [symfony/http-client](https://symfony.com/doc/current/components/http_client.html) to throttle requests subject to rate-limits.

## Installation

> composer require bentools/funnel-http-client:1.0.x-dev

## Usage

```php
use BenTools\FunnelHttpClient\FunnelHttpClient;
use Symfony\Component\HttpClient\HttpClient;

$client = FunnelHttpClient::throttle(HttpClient::create(), $maxRequests = 3, $timeWindow = 5);

$client->request('GET', 'http://foo.bar');
$client->request('GET', 'http://foo.bar');
$client->request('GET', 'http://foo.bar');

$client->request('GET', 'http://foo.bar'); // Will wait a little before being actually triggered
```

## Tests

> ./vendor/bin/phpunit

## License

MIT.
