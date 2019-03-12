<?php

declare(strict_types=1);

namespace UMA\Hydra\Tests\Fixtures;

use PHPUnit\Framework\Assert;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use UMA\Hydra;

/**
 * A fake Callback that throws an exception in order to
 * test the cleanup mechanism in the Client.
 */
final class TerroristCallback implements Hydra\Callback
{
    /**
     * @var Assert
     */
    private $assert;

    /**
     * @var int
     */
    private $counter;

    public function __construct(Assert $assert)
    {
        $this->assert = $assert;
        $this->counter = 0;
    }

    public function handle(RequestInterface $request, ?ResponseInterface $response, Hydra\CurlStats $stats): void
    {
        $this->counter++;

        throw new TerroristException;
    }

    public function expectedCount(int $expected): void
    {
        $this->assert::assertSame($expected, $this->counter);
    }
}
