<?php

declare(strict_types=1);

namespace UMA\Hydra;

final class ClientOptions
{
    private const HYDRA_VERSION = '0.1.0';

    /**
     * @var int
     *
     * @see https://curl.haxx.se/libcurl/c/CURLOPT_CONNECTTIMEOUT_MS.html
     */
    public $connectionTimeout = 300000;

    /**
     * @var int
     *
     * @see https://curl.haxx.se/libcurl/c/CURLOPT_TIMEOUT_MS.html
     */
    public $responseTimeout = 0;

    /**
     * @var int
     *
     * @see https://curl.haxx.se/libcurl/c/CURLOPT_DNS_CACHE_TIMEOUT.html
     */
    public $dnsCacheTtl = 60;

    /**
     * @var bool
     */
    public $persistentPool = false;

    /**
     * @var int|null
     */
    public $fixedPool;

    /**
     * @var string|null
     */
    public $proxyUrl;

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
        $this->userAgent = self::defaultUserAgent();
    }

    /**
     * @example 'hydra/0.1.0 curl/7.64.0 PHP/7.3.2'
     */
    private static function defaultUserAgent(): string
    {
        return \sprintf('hydra/%s curl/%s PHP/%s', self::HYDRA_VERSION, \curl_version()['version'], PHP_VERSION);
    }
}
