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
    private $conns;

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
        $this->conns = [];
        $this->multi = \curl_multi_init();
        $this->options = $options;
    }

    public function add(Connection $connection): void
    {
        $connection->handle = CurlAdapter::curlify(
            $connection->request,
            $this->options,
            $connection->responseHeaders
        );

        \curl_multi_add_handle($this->multi, $connection->handle);

        $this->conns[(int) $connection->handle] = $connection;
    }

    public function pick(): Connection
    {
        do {
            \curl_multi_exec($this->multi, $_);
            \curl_multi_select($this->multi);
            $info = \curl_multi_info_read($this->multi);
        } while (false === $info);

        $id = (int) $info['handle'];
        $this->conns[$id]->stats = CurlStats::fromMultiInfo($info);

        \curl_multi_remove_handle($this->multi, $this->conns[$id]->handle);

        return $this->conns[$id];
    }

    public function reuse(Connection $oldConnection, Connection $newConnection): void
    {
        $id = (int) $oldConnection->handle;

        $this->conns[$id]->request = $newConnection->request;
        $this->conns[$id]->callback = $newConnection->callback;
        $this->conns[$id]->responseHeaders = [];

        $this->conns[$id]->handle = CurlAdapter::reusatron(
            $this->conns[$id]->handle,
            $this->conns[$id]->request,
            $this->options,
            $this->conns[$id]->responseHeaders
        );

        \curl_multi_add_handle($this->multi, $this->conns[$id]->handle);
    }

    public function shutdown(): void
    {
        if ($this->inactive()) {
            return;
        }

        foreach ($this->conns as $connection) {
            \curl_close($connection->handle);
        }

        $this->conns = [];

        \curl_multi_close($this->multi);

        $this->multi = null;
    }

    public function inactive(): bool
    {
        return null === $this->multi;
    }
}
