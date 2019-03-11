<?php

declare(strict_types=1);

namespace UMA\Hydra;

final class ClientOptions
{
    public const HYDRA_USER_AGENT = 'hydra/0.1.0';

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
    private $connectionTimeout = self::DEFAULT_CONNECTION_TIMEOUT;

    /**
     * @var int
     */
    private $responseTimeout = self::DEFAULT_RESPONSE_TIMEOUT;

    /**
     * @var int
     */
    private $dnsCaching = self::DEFAULT_DNS_CACHE_TIMEOUT;

    /**
     * @var string|null
     */
    private $proxyUrl;

    /**
     * @var string
     */
    private $userAgent;

    /**
     * @var array
     */
    private $customOpts = [];

    public function __construct()
    {
        $this->userAgent = self::defaultUserAgent();
    }

    public function withCustomConnectionTimeout(int $milliSeconds): ClientOptions
    {
        $this->connectionTimeout = $milliSeconds;

        return $this;
    }

    public function withCustomResponseTimeout(int $milliSeconds): ClientOptions
    {
        $this->responseTimeout = $milliSeconds;

        return $this;
    }

    public function withCustomDnsCacheTimeout(int $seconds): ClientOptions
    {
        $this->dnsCaching = $seconds;

        return $this;
    }

    public function withDisabledDnsCaching(): ClientOptions
    {
        $this->dnsCaching = 0;

        return $this;
    }

    public function withProxy(string $proxyUrl): ClientOptions
    {
        $this->proxyUrl = $proxyUrl;

        return $this;
    }

    public function withCustomUserAgent(string $userAgent): ClientOptions
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function withCustomCurlOption(int $option, $value): ClientOptions
    {
        $this->customOpts[$option] = $value;

        return $this;
    }

    public function connectionTimeout(): int
    {
        return $this->connectionTimeout;
    }

    public function responseTimeout(): int
    {
        return $this->responseTimeout;
    }

    public function dnsCacheTimeout(): int
    {
        return $this->dnsCaching;
    }

    public function proxyUrl(): ?string
    {
        return $this->proxyUrl;
    }

    public function userAgent(): string
    {
        return $this->userAgent;
    }

    public function customOptions(): array
    {
        return $this->customOpts;
    }

    /**
     * @example 'hydra/0.1.0 curl/7.64.0 PHP/7.3.2'
     */
    private static function defaultUserAgent(): string
    {
        return \sprintf('%s curl/%s PHP/%s', self::HYDRA_USER_AGENT, \curl_version()['version'], PHP_VERSION);
    }
}
