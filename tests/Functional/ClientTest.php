<?php

declare(strict_types=1);

namespace UMA\Hydra\Tests\Functional;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
use UMA\Hydra\Client;
use UMA\Hydra\ClientOptions;
use UMA\Hydra\Tests\Fixtures\TestingHandler;

final class ClientTest extends TestCase
{
    /**
     * @var TestingHandler
     */
    private $handler;

    /**
     * @var Client
     */
    private $client;

    protected function setUp(): void
    {
        $this->handler = new TestingHandler($this);
        $this->client = new Client(new ClientOptions);
    }

    public function testNoRequests(): void
    {
        $this->client->send();

        self::assertTrue(true);
    }

    public function testOneSuccessfulRequest(): void
    {
        $this->client->load(new Request('GET', 'http://sleepy:1234'), $this->handler);

        $this->handler->expectedCount(0);

        $this->client->send();

        $this->handler->expectedCount(1);
    }

    public function testMultipleSuccessfulRequests(): void
    {
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->handler);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->handler);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->handler);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->handler);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->handler);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->handler);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->handler);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->handler);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->handler);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->handler);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->handler);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->handler);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->handler);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->handler);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->handler);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->handler);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->handler);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->handler);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->handler);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=50'), $this->handler);

        $this->handler->expectedCount(0);

        $start = self::getCurrentTimeMs();
        $this->client->send();
        $end = self::getCurrentTimeMs();

        $this->handler->expectedCount(20);

        self::assertLessThan(100000, $end - $start, 'This test should take roughly over 50ms, but took more than 100ms');
    }

    public function testRequestWithBody(): void
    {
        $this->client->load(new Request('POST', 'http://sleepy:1234?ms=50', ['Content-Type' => 'application/json'], '{"foo": "bar"}'), $this->handler);
        $this->client->load(new Request('POST', 'http://sleepy:1234?ms=50', ['Content-Type' => 'application/json'], '{"foo": "bar"}'), $this->handler);
        $this->client->send();

        $this->handler->expectedCount(2);

        $stats = $this->handler->lastStats();
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
        $customOptions->persistentPool = true;
        $customOptions->fixedPool = 1;

        $customClient = new Client($customOptions);
        $customClient->load(new Request('GET', 'http://sleepy:1234?ms=150'), $this->handler);
        $customClient->load(new Request('GET', 'http://sleepy:1234?ms=150'), $this->handler);
        $customClient->send();

        self::assertSame(CURLE_OPERATION_TIMEDOUT, $this->handler->lastStats()->error_code);
        self::assertSame(0.0, $this->handler->lastStats()->waitingTime());
        self::assertSame(0.0, $this->handler->lastStats()->receivingTime());
        self::assertNull($this->handler->lastResponse());

        $customClient->load(new Request('GET', 'http://sleepy:1234'), $this->handler);
        $customClient->load(new Request('GET', 'http://sleepy:1234'), $this->handler);
        $customClient->send();

        self::assertSame(CURLE_OK, $this->handler->lastStats()->error_code);
        self::assertGreaterThan(0.0, $this->handler->lastStats()->waitingTime());
        self::assertGreaterThan(0.0, $this->handler->lastStats()->receivingTime());
        self::assertNotNull($this->handler->lastResponse());
    }

    private static function getCurrentTimeMs(): int
    {
        return (int)(\microtime(true) * 10**6);
    }
}
