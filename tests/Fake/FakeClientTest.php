<?php

declare(strict_types=1);

namespace UMA\Hydra\Tests\Fake;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
use UMA\Hydra\Fake\FakeClient;
use UMA\Hydra\Tests\Fixtures\TestingCallback;
use UMA\Hydra\Tests\Fixtures\TestingFakeHandler;

final class FakeClientTest extends TestCase
{
    public function testIt(): void
    {
        $callback = new TestingCallback($this);
        $client = new FakeClient(new TestingFakeHandler());
        $client->load(new Request('GET', 'https://www.google.com/'), $callback);
        $client->send();

        $callback->expectedCount(1);
        self::assertSame('fine.', (string) $callback->lastResponse()->getBody());
    }
}
