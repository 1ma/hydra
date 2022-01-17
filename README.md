# uma/hydra

[![Build Status](https://gitlab.com/1ma/hydra/badges/master/pipeline.svg)](https://gitlab.com/1ma/hydra/pipelines)
[![Code Coverage](https://gitlab.com/1ma/hydra/badges/master/coverage.svg)](https://gitlab.com/1ma/hydra/commits/master)

Hydra is a simple cURL-based concurrent HTTP client abstracted behind an easy to use PSR-7 wrapper.


## Install

Via Composer

``` bash
composer require uma/hydra
```


## Quick Demo

In a few words, you call `load` repeatedly passing a PSR-7 request and the service that will handle
its response. Once you've loaded all the requests you want to send at once, call `sendAll`. While
this method runs it will call each handler in the order it receives the responses.
`sendAll` blocks until all responses are received and their handlers have been executed.

Moreover, the client never throws exceptions (any encountered errors are notified to the relevant
handler without interrupting the execution flow).

```php
<?php

use Nyholm\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use UMA\Hydra;

require_once __DIR__ . '/vendor/autoload.php';

class DemoHandler implements Hydra\ResponseHandler {
    public function handle(
        RequestInterface $request,
        ?ResponseInterface $response,
        Hydra\CurlStats $stats
    ): void
    {
        echo \sprintf(
            "%b %s %s %s\n",
            null === $response,
            $stats->error_code,
            $stats->total_time,
            (string) $request->getUri()
        );
    }
}

$time = \microtime(true);

$client = new Hydra\Client();
$handler = new DemoHandler();

$client->load(new Request('GET', 'https://www.google.com/'), $handler);
$client->load(new Request('GET', 'https://packagist.org/'), $handler);
$client->load(new Request('GET', 'https://invalid.doma.in/'), $handler);

$client->sendAll();
// 1 6 0.009291 https://invalid.doma.in/
// 0 0 0.162465 https://www.google.com/
// 0 0 0.26687 https://packagist.org/

echo \sprintf("\nTotal elapsed time: %s\n", \microtime(true) - $time);
// Total elapsed time: 0.27728295326233
```


## Documentation

### Client behaviour

The `Client` accepts a variable number of PSR-7 requests with its `load` method, but does not send them straight away. The
second parameter for `load` is the object that will handle the response, and it must implement the `ResponseHandler` interface.
ResponseHandlers need to be implemented by the end user of the library, and the same instance can be reused in different calls to `load`.

Once all requests are loaded, calling `sendAll` will send all them at once, and run their handlers in the same order that the
responses are received. `sendAll` itself is blocking (execution won't move on until all requests have been handled) and does
not return anything. `Client` should never throw an exception, regardless of the outcome of each request.

### ResponseHandler API

`ResponseHandler` objects receive 3 arguments in their `handle` method when the `Client` is finished with their associated request.

The first one is the same PSR-7 request instance that was supplied to `load`, and is useful in order to have the context of which
response the handler is processing.

The second one is a PSR-7 response object. It will be null if cURL did not manage to complete the HTTP requests or any given reason.
For this reason it is advised to check the cURL error code in `$stats->error_code` before doing anything else. You MUST never throw
an exception from a handler, as this will prevent other handlers from running.

The third parameter, a `CurlStats` object is a DTO with all the statistics available to cURL about the request.

Besides all the elements documented [here], `CurlStats` also has an `error_code` attribute with the cURL error code and a couple of helpers
to get information that can be easily derived from the raw statistics, such as the remote server processing time.

### ClientOptions

`Client` can be further customised with an instance of the `ClientOptions` object that accepts at construction time.

```php
// Time amounts have to be specified in milliseconds.
$customOptions = (new ClientOptions)
    ->withDisabledDNSCaching()
    ->withCustomConnectionTimeout(100)
    ->withCustomResponseTimeout(100)
    ->withProxy('http://hoverfly.local:8500');

$customClient = new Client($customOptions);
```


## FAQ and Tips

### How does Hydra compare to Guzzle?

### How many concurrent requests can be sent?

### Why does Hydra depend on the `nyholm/psr7` package?

### Why is Hydra not PSR-18 compatible?

### How to write ResponseHandlers


## Testing

Run them with `composer test`. They need docker and docker-compose available.


[here]: (http://php.net/manual/en/function.curl-getinfo.php)
