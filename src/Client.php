<?php

declare(strict_types=1);

namespace UMA\Hydra;

use Psr\Http\Message\RequestInterface;
use UMA\Hydra\Internal\CurlAdapter;
use UMA\Hydra\Internal\PsrAdapter;

final class Client implements ClientInterface
{
    /**
     * @var array
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
        $this->pool = [];
        $this->options = $options ?? new ClientOptions;
    }

    public function load(RequestInterface $request, Callback $callback): void
    {
        $this->backlog[] = [$request, $callback];
    }

    public function sendAll(): void
    {
        $handledRequests = 0;
        $totalRequests = \count($this->backlog);

        $multi = $this->initPool();

        while ($handledRequests < $totalRequests) {
            \curl_multi_exec($multi, $active);
            \curl_multi_select($multi);
            $info = \curl_multi_info_read($multi);

            if (false === $info) {
                continue;
            }

            $id = (int) $info['handle'];
            $stats = CurlStats::fromMultiInfo($info);

            \curl_multi_remove_handle($multi, $this->pool[$id]['handle']);

            try {
                $this->pool[$id]['callback']->handle(
                    $this->pool[$id]['request'],
                    PsrAdapter::psr7fy(
                        $info['handle'],
                        $this->pool[$id]['response_headers'],
                        $stats->http_code
                    ),
                    $stats
                );
            } catch (\Throwable $exception) {
                $this->backlog = [];
                $this->pool = [];
                \curl_multi_close($multi);

                throw $exception;
            }

            if (!empty($this->backlog)) {
                [$request, $callback] = \array_shift($this->backlog);

                $this->pool[$id]['callback'] = $callback;
                $this->pool[$id]['request'] = $request;
                $this->pool[$id]['response_headers'] = [];
                $this->pool[$id]['handle'] = CurlAdapter::reusatron(
                    $this->pool[$id]['handle'],
                    $request,
                    $this->options,
                    $this->pool[$id]['response_headers']
                );

                \curl_multi_add_handle($multi, $this->pool[$id]['handle']);
            } else {
                \curl_close($this->pool[$id]['handle']);
            }

            $handledRequests++;
        }

        $this->pool = [];
        \curl_multi_close($multi);
    }

    /**
     * @return resource
     */
    private function initPool()
    {
        $multi = curl_multi_init();
        $maxPoolSize = $this->options->poolSize(\count($this->backlog));

        while (!empty($this->backlog) && \count($this->pool) < $maxPoolSize) {
            [$request, $callback] = \array_shift($this->backlog);

            $connection = [
                'callback' => $callback,
                'request' => $request,
                'handle' => null,
                'response_headers' => []
            ];

            $connection['handle'] = CurlAdapter::curlify($request, $this->options, $connection['response_headers']);
            $this->pool[(int) $connection['handle']] = $connection;

            \curl_multi_add_handle($multi, $connection['handle']);
        }

        return $multi;
    }
}
