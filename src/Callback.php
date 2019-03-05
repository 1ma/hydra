<?php

declare(strict_types=1);

namespace UMA\Hydra;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface Callback
{
    /**
     * Handle the incoming HTTP response.
     */
    public function handle(RequestInterface $request, ?ResponseInterface $response, CurlStats $stats): void;
}
