<?php

declare(strict_types=1);

namespace UMA\Hydra\Internal\Curl;

use UMA\Hydra\ClientOptions;
use UMA\Hydra\CurlStats;
use UMA\Hydra\Internal\Job;

/**
 * @internal This class is not part of the package API. Don't use it directly.
 */
final class Pool
{
    /**
     * @var Job[]
     */
    private $pool;

    /**
     * @var resource
     */
    private $multi;

    /**
     * @var ClientOptions
     */
    private $options;

    public function __construct(ClientOptions $options)
    {
        $this->pool = [];
        $this->multi = \curl_multi_init();
        $this->options = $options;
    }

    public function init(Job ...$newJobs): void
    {
        \assert($this->active());
        \assert(\count($newJobs) <= $this->size());

        $i = 0;
        foreach ($this->pool as $oldJob) {
            $this->recycle($oldJob, $newJobs[$i++]);

            if (!isset($newJobs[$i])) {
                break;
            }
        }
    }

    public function add(Job $job): void
    {
        \assert($this->active());
        \assert(!$this->full());

        $job->handle = Adapter::curlify(
            $job->request,
            $this->options,
            $job->responseHeaders
        );

        \curl_multi_add_handle($this->multi, $job->handle);

        $this->pool[(int) $job->handle] = $job;
    }

    public function pick(): Job
    {
        \assert($this->active());

        do {
            \curl_multi_exec($this->multi, $_);
            \curl_multi_select($this->multi);
            $info = \curl_multi_info_read($this->multi);
        } while (false === $info);

        $id = (int) $info['handle'];
        $this->pool[$id]->stats = CurlStats::fromMultiInfo($info);

        \curl_multi_remove_handle($this->multi, $this->pool[$id]->handle);

        return $this->pool[$id];
    }

    public function recycle(Job $old, Job $new): void
    {
        \assert($this->active());

        $id = (int) $old->handle;

        $this->pool[$id]->request = $new->request;
        $this->pool[$id]->callback = $new->callback;
        $this->pool[$id]->responseHeaders = [];

        $this->pool[$id]->handle = Adapter::reusatron(
            $this->pool[$id]->handle,
            $this->pool[$id]->request,
            $this->options,
            $this->pool[$id]->responseHeaders
        );

        \curl_multi_add_handle($this->multi, $this->pool[$id]->handle);
    }

    public function shutdown(): void
    {
        \assert($this->active());

        foreach ($this->pool as $job) {
            \curl_close($job->handle);
        }

        $this->pool = [];

        \curl_multi_close($this->multi);

        $this->multi = null;
    }

    public function active(): bool
    {
        return null !== $this->multi;
    }

    public function size(): int
    {
        return \count($this->pool);
    }

    public function full(): bool
    {
        return $this->options->fixedPool !== null
            && $this->options->fixedPool === \count($this->pool);
    }
}
