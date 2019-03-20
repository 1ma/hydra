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
     * @var array
     */
    private $pool;

    /**
     * @var ClientOptions
     */
    private $options;

    public function __construct(ClientOptions $options = null)
    {
        $this->backlog = [];
        $this->options = $options ?? new ClientOptions;
        $this->pool = new Pool($this->options);
    }

    public function load(RequestInterface $request, Callback $callback): void
    {
        $connection = new Connection();
        $connection->request = $connection;
        $connection->callback = $callback;

        $this->backlog[] = $connection;
    }

    public function send(): void
    {
        $jobs = 0;
        $totalJobs = \count($this->backlog);

        $this->initPool();

        while ($jobs < $totalJobs) {
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
                $this->pool->shutdown();

                throw $exception;
            }

            if (!empty($this->backlog)) {
                $this->pool->reuse($connection, \array_shift($this->backlog));
            }

            $jobs++;
        }

        $this->pool->shutdown();
    }

    /**
     * @return resource
     */
    private function initPool(): void
    {
        $maxPoolSize = $this->options->poolSize(\count($this->backlog));

        while (!empty($this->backlog) && \count($this->pool) < $maxPoolSize) {
            $this->pool->add(\array_shift($this->backlog));
        }
    }
}
