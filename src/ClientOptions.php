<?php

declare(strict_types=1);

namespace UMA\Hydra;

final class ClientOptions
{
    /**
     * Times are in milliseconds!
     */
    private const DEFAULT_CONNECTION_TIMEOUT = 500;
    private const DEFAULT_RESPONSE_TIMEOUT = 2000;

    /**
     * @var int
     */
    private $connectionTimeout = self::DEFAULT_CONNECTION_TIMEOUT;

    /**
     * @var int
     */
    private $responseTimeout = self::DEFAULT_RESPONSE_TIMEOUT;

    /**
     * @var bool
     */
    private $dnsCaching = true;

    /**
     * @var string|null
     */
    private $proxyUrl;

    public function withCustomConnectionTimeout(int $ms): ClientOptions
    {
        $this->connectionTimeout = $ms;

        return $this;
    }

    public function withCustomResponseTimeout(int $ms): ClientOptions
    {
        $this->responseTimeout = $ms;

        return $this;
    }

    public function withDisabledDNSCaching(): ClientOptions
    {
        $this->dnsCaching = false;

        return $this;
    }

    public function withProxy(string $proxyUrl): ClientOptions
    {
        $this->proxyUrl = $proxyUrl;

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

    public function dnsCachingEnabled(): bool
    {
        return $this->dnsCaching;
    }

    public function proxyUrl(): ?string
    {
        return $this->proxyUrl;
    }
}
