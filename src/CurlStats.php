<?php

declare(strict_types=1);

namespace UMA\Hydra;

/**
 * Strong typed DTO of the stats extracted from a cURL handler.
 */
final class CurlStats
{
    public $error_code;
    public $url;
    public $content_type;
    public $http_code;
    public $header_size;
    public $request_size;
    public $filetime;
    public $ssl_verify_result;
    public $redirect_count;
    public $total_time;
    public $namelookup_time;
    public $connect_time;
    public $pretransfer_time;
    public $size_upload;
    public $size_download;
    public $speed_download;
    public $speed_upload;
    public $download_content_length;
    public $upload_content_length;
    public $starttransfer_time;
    public $redirect_time;
    public $redirect_url;
    public $primary_ip;
    public $certinfo;
    public $primary_port;
    public $local_ip;
    public $local_port;

    /**
     * @param array An array returned by curl_multi_info_read()
     */
    public static function fromMultiInfo(array $multiInfo): CurlStats
    {
        $stats = new self;
        $stats->error_code = $multiInfo['result'];
        foreach (\curl_getinfo($multiInfo['handle']) as $key => $value) {
            $stats->$key = $value;
        }

        return $stats;
    }

    /**
     * The elapsed time (in seconds) from the moment cURL finished sending
     * the request to the server until it received the first byte of the response.
     */
    public function serverProcessingTime(): float
    {
        return $this->starttransfer_time - $this->pretransfer_time;
    }

    /**
     * The elapsed time (in seconds) from the moment cURL received the first
     * byte of the response until the response was fully received.
     */
    public function receivingTime(): float
    {
        return $this->total_time - $this->starttransfer_time;
    }

    /**
     * Helper method that returns the fieldset of relevant metrics to push to
     * Telegraf. The timings are also converted to milliseconds.
     */
    public function telegrafTimingsFieldSet(): array
    {
        return [
            'lookupTime' => (int)($this->namelookup_time * 10**3),
            'connectTime' => (int)($this->pretransfer_time * 10**3),
            'serverProcessingTime' => (int)($this->serverProcessingTime() * 10**3),
            'receiveTime' => (int)($this->receivingTime() * 10**3),
            'totalTime' => (int)($this->total_time * 10**3)
        ];
    }
}
