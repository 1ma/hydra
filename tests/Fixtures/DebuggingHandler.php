<?php

declare(strict_types=1);

namespace UMA\Hydra\Tests\Fixtures;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use UMA\Hydra;
use UMA\Hydra\CurlStats;

final class DebuggingHandler implements Hydra\ResponseHandler
{
    private $firstRun = true;

    private $lastTotalTime = 0.0;

    public function handle(RequestInterface $request, ?ResponseInterface $response, CurlStats $stats): void
    {
        if ($this->firstRun) {
            echo "curl_code | http_status | pre_transfer | wait_time | recv_time | total_time | request\n";
            echo "----------+-------------+--------------+-----------+-----------+------------+--------\n";

            $this->firstRun = false;
        }

        echo \sprintf(
            "%s | %s | %s | %s | %s | %s | %s\n",
            \str_pad((string) $stats->error_code, 9, ' ', STR_PAD_LEFT),
            \str_pad((string) $stats->http_code, 11, ' ', STR_PAD_LEFT),
            \str_pad(\sprintf('%.6f', $stats->pretransfer_time), 12, ' ', STR_PAD_LEFT),
            \str_pad(\sprintf('%.6f', $stats->waitingTime()), 9, ' ', STR_PAD_LEFT),
            \str_pad(\sprintf('%.6f', $stats->receivingTime()), 9, ' ', STR_PAD_LEFT),
            \str_pad(\sprintf('%.6f', $stats->total_time), 10, ' ', STR_PAD_LEFT),
            $request->getMethod() . ' ' . $request->getUri()
        );

        $this->lastTotalTime = $stats->total_time;
    }

    public function lastTotalTime(): float
    {
        return $this->lastTotalTime;
    }
}
