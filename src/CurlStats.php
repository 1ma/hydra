<?php

declare(strict_types=1);

namespace UMA\Hydra;

/**
 * Strong typed DTO of the stats extracted from a cURL handler.
 */
final class CurlStats
{
    /**
     * @var int
     */
    public $error_code;

    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $content_type;

    /**
     * @var int
     */
    public $http_code;

    /**
     * @var int
     */
    public $header_size;

    /**
     * @var int
     */
    public $request_size;

    /**
     * @var int
     */
    public $filetime;

    /**
     * @var int
     */
    public $ssl_verify_result;

    /**
     * @var int
     */
    public $redirect_count;

    /**
     * @var double
     */
    public $total_time;

    /**
     * @var double
     */
    public $namelookup_time;

    /**
     * @var double
     */
    public $connect_time;

    /**
     * @var double
     */
    public $pretransfer_time;

    /**
     * @var double
     */
    public $size_upload;

    /**
     * @var double
     */
    public $size_download;

    /**
     * @var double
     */
    public $speed_download;

    /**
     * @var double
     */
    public $speed_upload;

    /**
     * @var double
     */
    public $download_content_length;

    /**
     * @var double
     */
    public $upload_content_length;

    /**
     * @var double
     */
    public $starttransfer_time;

    /**
     * @var double
     */
    public $redirect_time;

    /**
     * @var string
     */
    public $redirect_url;

    /**
     * @var string
     */
    public $primary_ip;

    /**
     * @var array
     */
    public $certinfo;

    /**
     * @var int
     */
    public $primary_port;

    /**
     * @var string
     */
    public $local_ip;

    /**
     * @var int
     */
    public $local_port;

    /**
     * @param array An array returned by curl_multi_info_read()
     */
    public static function fromMultiInfo(array $multiInfo): CurlStats
    {
        $stats = new self;
        $stats->error_code = $multiInfo['result'];

        [
            'url' => $stats->url,
            'content_type' => $stats->content_type,
            'http_code' => $stats->http_code,
            'header_size' => $stats->header_size,
            'request_size' => $stats->request_size,
            'filetime' => $stats->filetime,
            'ssl_verify_result' => $stats->ssl_verify_result,
            'redirect_count' => $stats->redirect_count,
            'total_time' => $stats->total_time,
            'namelookup_time' => $stats->namelookup_time,
            'connect_time' => $stats->connect_time,
            'pretransfer_time' => $stats->pretransfer_time,
            'size_upload' => $stats->size_upload,
            'size_download' => $stats->size_download,
            'speed_download' => $stats->speed_download,
            'speed_upload' => $stats->speed_upload,
            'download_content_length' => $stats->download_content_length,
            'upload_content_length' => $stats->upload_content_length,
            'starttransfer_time' => $stats->starttransfer_time,
            'redirect_time' => $stats->redirect_time,
            'redirect_url' => $stats->redirect_url,
            'primary_ip' => $stats->primary_ip,
            'certinfo' => $stats->certinfo,
            'primary_port' => $stats->primary_port,
            'local_ip' => $stats->local_ip,
            'local_port' => $stats->local_port,
        ] = \curl_getinfo($multiInfo['handle']);

        return $stats;
    }

    /**
     * The elapsed time (in seconds) from the moment cURL finished sending
     * the request to the server until it received the first byte of the response.
     */
    public function timeToFirstByte(): float
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
}
