<?php

declare(strict_types=1);

namespace UMA\Hydra\Fake;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface RequestHandler
{
    public function fake(RequestInterface $request): ?ResponseInterface;
}
