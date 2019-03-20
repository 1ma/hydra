<?php

declare(strict_types=1);

namespace UMA\Hydra\Internal;

use Psr\Http\Message\RequestInterface;
use UMA\Hydra\Callback;
use UMA\Hydra\CurlStats;

final class Job
{
    /**
     * @var RequestInterface
     */
    public $request;

    /**
     * @var Callback
     */
    public $callback;

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
