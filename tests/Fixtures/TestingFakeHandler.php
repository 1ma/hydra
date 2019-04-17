<?php

declare(strict_types=1);

namespace UMA\Hydra\Tests\Fixtures;

use Nyholm\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use UMA\Hydra\Fake\RequestHandler;

final class TestingFakeHandler implements RequestHandler
{
    public function fake(RequestInterface $request): ?ResponseInterface
    {
        return new Response(200, ['Content-Type' => 'text/plain'], 'fine.');
    }
}
