<?php

declare(strict_types=1);

namespace UMA\Hydra\Internal;

use Psr\Http\Message\RequestInterface;
use UMA\Hydra\ResponseHandler;
use UMA\Hydra\CurlStats;

/**
 * @internal This class is not part of the package API. Don't use it directly.
 */
final class Job
{
    /**
     * @var RequestInterface
     */
    public $request;

    /**
     * @var ResponseHandler
     */
    public $handler;

    /**
     * @var array
     */
    public $responseHeaders = [];

    /**
     * @var resource
     */
    public $handle;

    /**
     * @var CurlStats
     */
    public $stats;
}
