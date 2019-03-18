<?php

declare(strict_types=1);

namespace UMA\Hydra;

final class ClientOptions
{
    private const HYDRA_VERSION = '0.1.0';

    /**
     * @see https://curl.haxx.se/libcurl/c/CURLOPT_CONNECTTIMEOUT_MS.html
     */
    private const DEFAULT_CONNECTION_TIMEOUT = 300000;

    /**
     * @see https://curl.haxx.se/libcurl/c/CURLOPT_TIMEOUT_MS.html
     */
    private const DEFAULT_RESPONSE_TIMEOUT = 0;

    /**
     * @see https://curl.haxx.se/libcurl/c/CURLOPT_DNS_CACHE_TIMEOUT.html
     */
    private const DEFAULT_DNS_CACHE_TIMEOUT = 60;

    /**
     * @var int
     */
    public $connectionTimeout;

    /**
     * @var int
     */
    public $responseTimeout;

    /**
     * @var int
     */
    public $dnsCacheTtl;

    /**
     * @var string|null
     */
    public $proxyUrl;

    /**
     * @var int|null
     */
    public $fixedPool;

    /**
     * @var string
     */
    public $userAgent;

    /**
     * @var array
     */
    public $customOpts = [];

    public function __construct()
    {
        $this->connectionTimeout = self::DEFAULT_CONNECTION_TIMEOUT;
        $this->responseTimeout = self::DEFAULT_RESPONSE_TIMEOUT;
        $this->dnsCacheTtl = self::DEFAULT_DNS_CACHE_TIMEOUT;
        $this->userAgent = self::defaultUserAgent();
    }

    public function poolSize(int $totalRequests): int
    {
        return \max(1, $this->fixedPool ?? $totalRequests);
    }

    /**
     * @example 'hydra/0.1.0 curl/7.64.0 PHP/7.3.2'
     */
    private static function defaultUserAgent(): string
    {
        return \sprintf('hydra/%s curl/%s PHP/%s', self::HYDRA_VERSION, \curl_version()['version'], PHP_VERSION);
    }
}
