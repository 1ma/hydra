<?php

declare(strict_types=1);

namespace UMA\Hydra;

use Psr\Http\Message\RequestInterface;
use UMA\Hydra\Internal\CurlAdapter;
use UMA\Hydra\Internal\PsrAdapter;

final class Client
{
    /**
     * @var resource
     */
    private $multiHandler;

    /**
     * @var array
     */
    private $backlog;

    /**
     * @var ClientOptions
     */
    private $options;

    public function __construct(ClientOptions $options = null)
    {
        $this->backlog = [];
        $this->options = $options ?? new ClientOptions;

        $this->blankState();
    }

    public function load(RequestInterface $request, Callback $callback): void
    {
        $entry = [
            'callback' => $callback,
            'request' => $request,
            'handle' => null,
            'response_headers' => []
        ];

        $entry['handle'] = CurlAdapter::curlify($request, $this->options, $entry['response_headers']);

        \curl_multi_add_handle($this->multiHandler, $entry['handle']);

        $this->backlog[(int) $entry['handle']] = $entry;
    }

    public function sendAll(): void
    {
        $handledReqs = 0;
        $backlogSize = \count($this->backlog);

        while ($handledReqs < $backlogSize) {
            \curl_multi_exec($this->multiHandler, $active);
            \curl_multi_select($this->multiHandler);
            $info = \curl_multi_info_read($this->multiHandler);

            if (false === $info) {
                continue;
            }

            \curl_multi_remove_handle($this->multiHandler, $info['handle']);

            $stats = CurlStats::fromMultiInfo($info);
            $entry = $this->backlog[(int) $info['handle']];

            try {
                $entry['callback']->handle(
                    $entry['request'],
                    PsrAdapter::psr7fy(
                        $info['handle'],
                        $entry['response_headers'],
                        $stats->http_code
                    ),
                    $stats
                );
            } catch (\Throwable $exception) {
                $this->blankState();

                throw $exception;
            }

            $handledReqs++;
        }

        $this->blankState();
    }

    /**
     * Gracefully close all cURL resources and clear all service state.
     */
    private function blankState(): void
    {
        foreach ($this->backlog as $entry) {
            \curl_close($entry['handle']);
        }

        if (null !== $this->multiHandler) {
            \curl_multi_close($this->multiHandler);
        }

        $this->backlog = [];
        $this->multiHandler = \curl_multi_init();
    }
}
