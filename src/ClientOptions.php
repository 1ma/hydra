<?php

declare(strict_types=1);

namespace UMA\Hydra;

final class ClientOptions
{
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
     * @var array
     */
    private $customOpts = [];

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

    public function customOptions(): array
    {
        return $this->customOpts;
    }
}
