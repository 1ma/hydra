<?php

declare(strict_types=1);

namespace UMA\Hydra;

use Psr\Http\Message\RequestInterface;

interface ClientInterface
{
    public function load(RequestInterface $request, ResponseHandler $handler): void;

    public function send(): void;
}
