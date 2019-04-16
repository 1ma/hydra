<?php

declare(strict_types=1);

namespace UMA\Hydra;

use Psr\Http\Message\RequestInterface;
use UMA\Hydra\Internal\Job;
use UMA\Hydra\Internal\Curl;
use UMA\Hydra\Internal\Psr;

final class Client implements ClientInterface
{
    /**
     * @var Job[]
     */
    private $backlog;

    /**
     * @var int
     */
    private $jobs;

    /**
     * @var ClientOptions
     */
    private $options;

    /**
     * @var Curl\Pool
     */
    private $pool;

    public function __construct(ClientOptions $options = null)
    {
        $this->backlog = [];
        $this->jobs = 0;
        $this->options = $options ?? new ClientOptions;
        $this->pool = new Curl\Pool($this->options);
    }

    public function load(RequestInterface $request, Callback $callback): void
    {
        $this->jobs++;

        if (!$this->pool->active()) {
            $this->pool = new Curl\Pool($this->options);
        }

        $job = new Job();
        $job->request = $request;
        $job->callback = $callback;

        if (!$this->pool->add($job)) {
            $this->backlog[] = $job;
        }
    }

    public function send(): void
    {
        $completed = 0;
        while ($completed < $this->jobs) {
            $job = $this->pool->pick();

            $job->callback->handle(
                $job->request,
                Psr\Adapter::psr7fy(
                    $job->handle,
                    $job->responseHeaders,
                    $job->stats->http_code
                ),
                $job->stats
            );

            if (!empty($this->backlog)) {
                $this->pool->recycle($job, \array_shift($this->backlog));
            }

            $completed++;
        }

        \assert(empty($this->backlog));

        $this->jobs = 0;
        $this->pool->shutdown();
    }
}
