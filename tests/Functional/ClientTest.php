<?php

declare(strict_types=1);

namespace UMA\Hydra\Tests\Functional;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
use UMA\Hydra\Client;
use UMA\Hydra\ClientOptions;
use UMA\Hydra\Tests\Fixtures\TerroristCallback;
use UMA\Hydra\Tests\Fixtures\TerroristException;
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
        $this->client->sendAll();

        self::assertTrue(true);
    }

    public function testOneSuccessfulRequest(): void
    {
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=0'), $this->callback);

        $this->callback->expectedCount(0);

        $this->client->sendAll();

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
        $this->client->sendAll();
        $end = self::getCurrentTimeMs();

        $this->callback->expectedCount(20);

        self::assertLessThan(100000, $end - $start, 'This test should take roughly over 50ms, but took more than 100ms');
    }

    public function testRequestWithBody(): void
    {
        $this->client->load(new Request('POST', 'http://sleepy:1234?ms=50', ['Content-Type' => 'application/json'], '{"foo": "bar"}'), $this->callback);
        $this->client->load(new Request('POST', 'http://sleepy:1234?ms=50', ['Content-Type' => 'application/json'], '{"foo": "bar"}'), $this->callback);
        $this->client->sendAll();

        $this->callback->expectedCount(2);

        $stats = $this->callback->lastStats();
        self::assertGreaterThan(0, $stats->receivingTime());
        self::assertGreaterThan(0, $stats->timeToFirstByte());
        self::assertLessThan(0.1, $stats->timeToFirstByte());
    }

    public function testProxyAndResponseTimeout(): void
    {
        $customOptions = (new ClientOptions)
            ->withDisabledDnsCaching()
            ->withCustomConnectionTimeout(100)
            ->withCustomResponseTimeout(100)
            ->withCustomDnsCacheTimeout(10)
            ->withCustomUserAgent('foo/1.2.3')
            ->withCustomCurlOption(CURLOPT_SSL_VERIFYPEER, 0)
            ->withProxy('http://hoverfly:8500');

        $customClient = new Client($customOptions);
        $customClient->load(new Request('GET', 'http://sleepy:1234?ms=150'), $this->callback);
        $customClient->sendAll();

        self::assertSame(CURLE_OPERATION_TIMEDOUT, $this->callback->lastStats()->error_code);
        self::assertNull($this->callback->lastResponse());
    }

    public function testRecoveryMechanism(): void
    {
        $callback = new TerroristCallback($this);

        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=0'), $callback);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=0'), $callback);
        $this->client->load(new Request('GET', 'http://sleepy:1234?ms=0'), $callback);

        try {
            $this->client->sendAll();

            self::fail('A TerroristException should have been thrown here');
        } catch (TerroristException $e) {
        }

        $callback->expectedCount(1);
    }

    private static function getCurrentTimeMs(): int
    {
        return (int)(\microtime(true) * 10**6);
    }
}
