<?php

declare(strict_types=1);

namespace UMA\Hydra\Tests\Fake;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
use UMA\Hydra\Fake\FakeClient;
use UMA\Hydra\Tests\Fixtures\TestingHandler;
use UMA\Hydra\Tests\Fixtures\TestingFakeHandler;

final class FakeClientTest extends TestCase
{
    public function testIt(): void
    {
        $handler = new TestingHandler($this);
        $client = new FakeClient(new TestingFakeHandler());
        $client->load(new Request('GET', 'https://www.google.com/'), $handler);
        $client->send();

        $handler->expectedCount(1);
        self::assertSame('fine.', (string) $handler->lastResponse()->getBody());
    }
}
