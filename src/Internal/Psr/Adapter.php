<?php

declare(strict_types=1);

namespace UMA\Hydra\Internal\Psr;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

final class Adapter
{
    /**
     * Returns an instance of a Psr7 ResponseInterface
     * functionally equivalent to the received cURL resource.
     *
     * This function will not close the cURL resource.
     */
    public static function psr7fy($handler, array $responseHeaders, int $statusCode): ?ResponseInterface
    {
        return 0 === $statusCode ? null : new Response(
            $statusCode,
            $responseHeaders,
            \curl_multi_getcontent($handler)
        );
    }
}
