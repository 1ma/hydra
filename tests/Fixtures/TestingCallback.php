<?php

declare(strict_types=1);

namespace UMA\Hydra\Tests\Fixtures;

use PHPUnit\Framework\Assert;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use UMA\Hydra\Callback;
use UMA\Hydra\CurlStats;

/**
 * A fixture callback that counts
 */
final class TestingCallback implements Callback
{
    /**
     * @var Assert
     */
    private $assert;

    /**
     * @var ResponseInterface|null
     */
    private $lastResponse;

    /**
     * @var CurlStats
     */
    private $lastStats;

    /**
     * @var int
     */
    private $counter;

    public function __construct(Assert $assert)
    {
        $this->assert = $assert;
        $this->counter = 0;
    }

    public function handle(RequestInterface $request, ?ResponseInterface $response, CurlStats $stats): void
    {
        $this->lastResponse = $response;
        $this->lastStats = $stats;
        $this->counter++;
    }

    public function expectedCount(int $expected): void
    {
        $this->assert::assertSame($expected, $this->counter);
    }

    public function lastResponse(): ?ResponseInterface
    {
        return $this->lastResponse;
    }

    public function lastStats(): CurlStats
    {
        return $this->lastStats;
    }
}
