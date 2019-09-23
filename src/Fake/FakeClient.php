<?php

declare(strict_types=1);

namespace UMA\Hydra\Fake;

use Psr\Http\Message\RequestInterface;
use UMA\Hydra\ResponseHandler;
use UMA\Hydra\ClientInterface;
use UMA\Hydra\CurlStats;

final class FakeClient implements ClientInterface
{
    /**
     * @var RequestHandler
     */
    private $fakeHandler;

    /**
     * @var array
     */
    private $jobs;

    public function __construct(RequestHandler $handler)
    {
        $this->fakeHandler = $handler;
        $this->jobs = [];
    }

    public function load(RequestInterface $request, ResponseHandler $handler): void
    {
        $this->jobs[] = [$request, $handler];
    }

    public function send(): void
    {
        $fakeStats = new CurlStats();
        $fakeStats->namelookup_time = 0.0;
        $fakeStats->connect_time = 0.0;
        $fakeStats->appconnect_time = 0.0;
        $fakeStats->pretransfer_time = 0.0;
        $fakeStats->starttransfer_time = 0.0;
        $fakeStats->total_time = 0.0;

        /**
         * @var RequestInterface $request
         * @var ResponseHandler $handler
         */
        foreach ($this->jobs as [$request, $handler]) {
            $fakeResponse = $this->fakeHandler->fake($request);

            $fakeStats->error_code = null === $fakeResponse ? CURLE_COULDNT_CONNECT : CURLE_OK;
            $fakeStats->http_code = null === $fakeResponse ? 0 : $fakeResponse->getStatusCode();

            $handler->handle($request, $fakeResponse, $fakeStats);
        }

        $this->jobs = [];
    }
}
