<?php

declare(strict_types=1);

namespace UMA\Hydra\Internal;

use UMA\Hydra\ClientOptions;
use UMA\Hydra\CurlStats;

final class Pool
{
    /**
     * @var Connection[]
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

    public function add(Connection $connection): void
    {
        \assert($this->active());

        $connection->handle = CurlAdapter::curlify(
            $connection->request,
            $this->options,
            $connection->responseHeaders
        );

        \curl_multi_add_handle($this->multi, $connection->handle);

        $this->pool[(int) $connection->handle] = $connection;
    }

    public function pick(): Connection
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

    public function recycle(Connection $old, Connection $new): void
    {
        \assert($this->active());

        $id = (int) $old->handle;

        $this->pool[$id]->request = $new->request;
        $this->pool[$id]->callback = $new->callback;
        $this->pool[$id]->responseHeaders = [];

        $this->pool[$id]->handle = CurlAdapter::reusatron(
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

        foreach ($this->pool as $connection) {
            \curl_close($connection->handle);
        }

        $this->pool = [];

        \curl_multi_close($this->multi);

        $this->multi = null;
    }

    public function size(): int
    {
        return \count($this->pool);
    }

    public function active(): bool
    {
        return null !== $this->multi;
    }
}
