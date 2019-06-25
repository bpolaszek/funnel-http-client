[![Latest Stable Version](https://poser.pugx.org/bentools/funnel-http-client/v/stable)](https://packagist.org/packages/bentools/funnel-http-client)
[![License](https://poser.pugx.org/bentools/funnel-http-client/license)](https://packagist.org/packages/bentools/funnel-http-client)
[![Build Status](https://img.shields.io/travis/bpolaszek/funnel-http-client/master.svg?style=flat-square)](https://travis-ci.org/bpolaszek/funnel-http-client)
[![Coverage Status](https://coveralls.io/repos/github/bpolaszek/funnel-http-client/badge.svg?branch=master)](https://coveralls.io/github/bpolaszek/funnel-http-client?branch=master)
[![Quality Score](https://img.shields.io/scrutinizer/g/bpolaszek/funnel-http-client.svg?style=flat-square)](https://scrutinizer-ci.com/g/bpolaszek/funnel-http-client)
[![Total Downloads](https://poser.pugx.org/bentools/funnel-http-client/downloads)](https://packagist.org/packages/bentools/funnel-http-client)

# Funnel Http Client

A decorator for [symfony/http-client](https://symfony.com/doc/current/components/http_client.html) to throttle requests subject to rate-limits.

## Installation

> composer require bentools/funnel-http-client:1.0.x-dev

## Usage

```php
use BenTools\FunnelHttpClient\FunnelHttpClient;
use BenTools\FunnelHttpClient\Storage\ArrayStorage;
use Symfony\Component\HttpClient\HttpClient;

$storage = new ArrayStorage($maxRequests = 10, $timeWindow = 60);
$client = new FunnelHttpClient(HttpClient::create(), $storage);

$client->request('GET', 'http://foo.bar');
```

## Tests

> ./vendor/bin/phpunit

## License

MIT.
