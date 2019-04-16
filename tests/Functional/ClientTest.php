<?php

declare(strict_types=1);

namespace UMA\Hydra\Tests\Functional;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
use UMA\Hydra\Client;
use UMA\Hydra\ClientOptions;
use UMA\Hydra\Tests\Fixtures\TestingCallback;

final class ClientTest extends TestCase
{
    /**
     * @var TestingCallback
     */
    private $callback;

    /**
     * @var Client
     */
    private $client;

    protected function setUp(): void
    {
        $this->callback = new TestingCallback($this);
        $this->client = new Client(new ClientOptions);
    }

    public function testNoRequests(): void
    {
        $this->client->send();

        self::assertTrue(true);
    }

    public function testOneSuccessfulRequest(): void
    {
        $this->client->load(new Request('GET', 'http://sleepy:1234'), $this->callback);

        $this->callback->expectedCount(0);

        $this->client->send();

        $this->callback->expectedCount(1);
    }

    public function testMultipleSuccessfulRequests(): void
    {
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->callback);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->callback);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->callback);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->callback);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->callback);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->callback);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->callback);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->callback);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->callback);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->callback);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->callback);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->callback);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->callback);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->callback);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->callback);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->callback);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->callback);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->callback);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->callback);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->callback);

        $this->callback->expectedCount(0);

        $start = self::getCurrentTimeMs();
        $this->client->send();
        $end = self::getCurrentTimeMs();

        $this->callback->expectedCount(20);

        self::assertLessThan(100000, $end - $start, 'This test should take roughly over 50ms, but took more than 100ms');
    }

    public function testRequestWithBody(): void
    {
        $this->client->load(new Request('POST', 'http://sleepy:1234?ms=50', ['Content-Type' => 'application/json'], '{"foo": "bar"}'), $this->callback);
        $this->client->load(new Request('POST', 'http://sleepy:1234?ms=50', ['Content-Type' => 'application/json'], '{"foo": "bar"}'), $this->callback);
        $this->client->send();

        $this->callback->expectedCount(2);

        $stats = $this->callback->lastStats();
        self::assertGreaterThan(0, $stats->receivingTime());
        self::assertGreaterThan(0, $stats->waitingTime());
        self::assertLessThan(0.1, $stats->waitingTime());
    }

    public function testProxyAndResponseTimeout(): void
    {
        $customOptions = new ClientOptions();
        $customOptions->dnsCacheTtl = 0;
        $customOptions->connectionTimeout = 100;
        $customOptions->responseTimeout = 100;
        $customOptions->userAgent = 'foo/1.2.3';
        $customOptions->customOpts = [CURLOPT_VERBOSE => false];
        $customOptions->proxyUrl = 'http://hoverfly:8500';
        $customOptions->fixedPool = 1;

        $customClient = new Client($customOptions);
        $customClient->load(new Request('GET', 'http://sleepy:1234?ms=150'), $this->callback);
        $customClient->load(new Request('GET', 'http://sleepy:1234?ms=150'), $this->callback);
        $customClient->send();

        self::assertSame(CURLE_OPERATION_TIMEDOUT, $this->callback->lastStats()->error_code);
        self::assertNull($this->callback->lastResponse());

        $customClient->load(new Request('GET', 'http://sleepy:1234'), $this->callback);
        $customClient->load(new Request('GET', 'http://sleepy:1234'), $this->callback);
        $customClient->send();

        self::assertSame(CURLE_OK, $this->callback->lastStats()->error_code);
        self::assertNotNull($this->callback->lastResponse());
    }

    private static function getCurrentTimeMs(): int
    {
        return (int)(\microtime(true) * 10**6);
    }
}
