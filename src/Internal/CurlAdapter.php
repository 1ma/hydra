<?php

declare(strict_types=1);

namespace UMA\Hydra\Internal;

use Psr\Http\Message\RequestInterface;
use UMA\Hydra\ClientOptions;

final class CurlAdapter
{
    public static function curlify(RequestInterface $request, ClientOptions $options, array &$responseHeaders)
    {
        return self::reusatron(\curl_init(), $request, $options, $responseHeaders);
    }

    /**
     * Returns a cURL handle functionally equivalent to
     * the received Psr7 HTTP request, combined with the
     * settings of the ClientOptions object.
     *
     * @return resource
     */
    public static function reusatron($handle, RequestInterface $request, ClientOptions $options, array &$responseHeaders)
    {
        \curl_setopt($handle, CURLOPT_URL, (string) $request->getUri());
        \curl_setopt($handle, CURLOPT_HEADER, false);
        \curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($handle, CURLOPT_USERAGENT, $options->userAgent());
        \curl_setopt($handle, CURLOPT_CONNECTTIMEOUT_MS, $options->connectionTimeout());
        \curl_setopt($handle, CURLOPT_TIMEOUT_MS, $options->responseTimeout());
        \curl_setopt($handle, CURLOPT_DNS_CACHE_TIMEOUT, $options->dnsCacheTimeout());

        foreach ($options->customOptions() as $option => $value) {
            \curl_setopt($handle, $option, $value);
        }

        if (null !== $proxyUrl = $options->proxyUrl()) {
            \curl_setopt($handle, CURLOPT_PROXY, $proxyUrl);
        }

        \curl_setopt($handle, CURLOPT_HEADERFUNCTION, function($_, string $header) use (&$responseHeaders) {
            $length = \strlen($header);
            $parsed = \explode(':', $header, 2);

            if (\count($parsed) < 2) {
                return $length;
            }

            $name = \trim($parsed[0]);
            $responseHeaders[$name] = [\trim($parsed[1])];

            return $length;
        });

        $method = $request->getMethod();
        if ('GET' !== $method) {
            \curl_setopt($handle, CURLOPT_CUSTOMREQUEST, $method);
        }

        \curl_setopt($handle, CURLOPT_HTTPHEADER, self::curlHeaders($request));

        $body = (string) $request->getBody();
        if ('' !== $body) {
            \curl_setopt($handle, CURLOPT_POSTFIELDS, $body);
        }

        return $handle;
    }

    private static function curlHeaders(RequestInterface $request): array
    {
        $headers = [];
        foreach ($request->getHeaders() as $name => $values) {
            $headers[] = \sprintf('%s: %s', $name, \implode(',', $values));
        }

        return $headers;
    }
}
