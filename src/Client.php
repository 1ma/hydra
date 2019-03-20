<?php

declare(strict_types=1);

namespace UMA\Hydra;

use Psr\Http\Message\RequestInterface;
use UMA\Hydra\Internal\Connection;
use UMA\Hydra\Internal\Pool;
use UMA\Hydra\Internal\PsrAdapter;

final class Client implements ClientInterface
{
    /**
     * @var Connection[]
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
     * @var Pool
     */
    private $pool;

    public function __construct(ClientOptions $options = null)
    {
        $this->backlog = [];
        $this->jobs = 0;
        $this->options = $options ?? new ClientOptions;
        $this->pool = new Pool($this->options);
    }

    public function load(RequestInterface $request, Callback $callback): void
    {
        $this->jobs++;

        if (!$this->pool->active()) {
            $this->pool = new Pool($this->options);
        }

        $connection = new Connection();
        $connection->request = $request;
        $connection->callback = $callback;

        if (!$this->pool->add($connection)) {
            $this->backlog[] = $connection;
        }
    }

    public function send(): void
    {
        $done = 0;
        while ($done < $this->jobs) {
            $connection = $this->pool->pick();

            try {
                $connection->callback->handle(
                    $connection->request,
                    PsrAdapter::psr7fy(
                        $connection->handle,
                        $connection->responseHeaders,
                        $connection->stats->http_code
                    ),
                    $connection->stats
                );
            } catch (\Throwable $exception) {
                $this->backlog = [];
                $this->jobs = 0;
                $this->pool->shutdown();

                throw $exception;
            }

            if (!empty($this->backlog)) {
                $this->pool->recycle($connection, \array_shift($this->backlog));
            }

            $done++;
        }

        \assert(empty($this->backlog));

        $this->jobs = 0;
        $this->pool->shutdown();
    }
}
