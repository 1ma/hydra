# uma/hydra

The Hydra Bulk HTTP client.


## Install

Via Composer

``` bash
composer require uma/hydra
```


## Quick Demo

```php
$callback = new class implements Callback {
    public function handle(RequestInterface $request, ?ResponseInterface $response, CurlStats $stats): void
    {
        echo \sprintf("%s %s %s\n", $stats->error_code, $stats->total_time, (string) $request->getUri());
    }
};

$time = \microtime(true);

$bulkClient = new BulkClient(new ClientOptions());
$bulkClient->load(new Request('GET', 'https://www.google.com/'), $callback);
$bulkClient->load(new Request('GET', 'https://packagist.org/'), $callback);
$bulkClient->load(new Request('GET', 'https://invalid.doma.in/'), $callback);
$bulkClient->sendAll();
// 6 0.000133 https://invalid.doma.in/
// 0 0.147821 https://packagist.org/
// 0 0.17111 https://www.google.com/

echo \sprintf("\nTotal elapsed time: %s\n", \microtime(true) - $time);
// Total elapsed time: 0.17644190788269
```


## Documentation

### BulkClient behaviour

The `BulkClient` accepts a variable number of PSR-7 requests with its `load` method, but does not send them straight away. The
second parameter for `load` is the object that will handle the response, and it must implement the `Callback` interface.
Callbacks need to be implemented by the end user of the library, and the same instance can be reused in different calls to `load`.

Once all requests are loaded, calling `sendAll` will send all them at once, and run their callbacks in the same order that the
responses are received. `sendAll` itself is blocking (execution won't move on until all requests have been handled) and does
not return anything. `BulkClient` should never throw an exception, regardless of the outcome of each request.

### Callback API

`Callback` objects receive 3 arguments in their `handle` method when the `BulkClient` is finished with their associated request.

The first one is the same PSR-7 request instance that was supplied to `load`, and is useful in order to have the context of which
response the callback is processing.

The second one is a PSR-7 response object. It will be null if cURL did not manage to complete the HTTP requests or any given reason.
For this reason it is advised to check the cURL error code in `$stats->error_code` before doing anything else. You MUST never throw
an exception from a callback, as this will prevent other callbacks from running.

The third parameter, a `CurlStats` object is a DTO with all the statistics available to cURL about the request.

Besides all the elements documented [here], `CurlStats` also has an `error_code` attribute with the cURL error code and a couple of helpers
to get information that can be easily derived from the raw statistics, such as the remote server processing time.

### ClientOptions

`BulkClient` can be further customised with an instance of the `ClientOptions` object that accepts at construction time.

```php
// Time amounts have to be specified in milliseconds.
$customOptions = (new ClientOptions)
    ->withDisabledDNSCaching()
    ->withCustomConnectionTimeout(100)
    ->withCustomResponseTimeout(100)
    ->withProxy('http://hoverfly.local:8500');

$customClient = new BulkClient($customOptions);
```

## Testing

Run them with `composer test`. They need docker and docker-compose available.


[here]: (http://php.net/manual/en/function.curl-getinfo.php)
