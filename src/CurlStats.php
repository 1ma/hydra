<?php

declare(strict_types=1);

namespace UMA\Hydra;

/**
 * Strong typed DTO of the stats extracted from a cURL handler.
 */
final class CurlStats
{
    /**
     * The error code. 0 means OK.
     *
     * @see https://curl.haxx.se/libcurl/c/libcurl-errors.html
     *
     * @var int
     */
    public $error_code;

    /**
     * The URL that was fetched last. This is most meaningful if you've told
     * curl to follow location: headers.
     *
     * @var string
     */
    public $url;

    /**
     * The Content-Type of the requested document, if there was any.
     *
     * @var string
     */
    public $content_type;

    /**
     * The numerical response code that was found in the last retrieved HTTP(S)
     * or FTP(s) transfer.
     *
     * @var int
     */
    public $http_code;

    /**
     * The total amount of bytes of the downloaded headers.
     *
     * @var int
     */
    public $header_size;

    /**
     * The total amount of bytes that were sent in the HTTP request.
     *
     * @var int
     */
    public $request_size;

    /**
     * Modification time of the remote resource.
     *
     * @var int
     */
    public $filetime;

    /**
     * The result of the SSL peer certificate verification that was requested.
     * 0 means the verification was successful. (Added in 7.19.0)
     *
     * @var int
     */
    public $ssl_verify_result;

    /**
     * Number of redirects that were followed in the request. (Added in 7.12.3)
     *
     * @var int
     */
    public $redirect_count;

    /**
     * The total time, in seconds, that the full operation lasted.
     *
     * @var double
     */
    public $total_time;

    /**
     * The time, in seconds, it took from the start until the name resolving was
     * completed.
     *
     * @var double
     */
    public $namelookup_time;

    /**
     * The time, in seconds, it took from the start until the TCP connect to the
     * remote host (or proxy) was completed.
     *
     * @var double
     */
    public $connect_time;

    /**
     * The time, in seconds, it took from the start until the SSL/SSH/etc
     * connect/handshake to the remote host was completed. (Added in 7.19.0)
     *
     * @var double
     */
    public $appconnect_time;

    /**
     * The time, in seconds, it took from the start until the file transfer was
     * just about to begin. This includes all pre-transfer commands and
     * negotiations that are specific to the particular protocol(s) involved.
     *
     * @var double
     */
    public $pretransfer_time;

    /**
     * The total amount of bytes that were uploaded.
     *
     * @var double
     */
    public $size_upload;

    /**
     * The total amount of bytes that were downloaded.
     *
     * @var double
     */
    public $size_download;

    /**
     * The average download speed that curl measured for the complete download.
     * Bytes per second.
     *
     * @var double
     */
    public $speed_download;

    /**
     * The average upload speed that curl measured for the complete upload.
     * Bytes per second.
     *
     * @var double
     */
    public $speed_upload;

    /**
     * Content length of download, read from Content-Length: field.
     *
     * @var double
     */
    public $download_content_length;

    /**
     * Specified size of upload.
     *
     * @var double
     */
    public $upload_content_length;

    /**
     * The time, in seconds, it took from the start until the first byte was
     * just about to be transferred. This includes time_pretransfer and also the
     * time the server needed to calculate the result.
     *
     * @var double
     */
    public $starttransfer_time;

    /**
     * The time, in seconds, it took for all redirection steps including name
     * lookup, connect, pretransfer and transfer before the final transaction
     * was started. time_redirect shows the complete execution time for multiple
     * redirections. (Added in 7.12.3)
     *
     * @var double
     */
    public $redirect_time;

    /**
     * When an HTTP request was made without -L, --location to follow redirects
     * (or when --max-redir is met), this variable will show the actual URL a
     * redirect would have gone to. (Added in 7.18.2)
     *
     * @var string
     */
    public $redirect_url;

    /**
     * The remote IP address of the most recently done connection - can be
     * either IPv4 or IPv6. (Added in 7.29.0)
     *
     * @var string
     */
    public $primary_ip;

    /**
     * TLS certificate chain information.
     *
     * @var array
     */
    public $certinfo;

    /**
     * The remote port number of the most recently done connection.
     * (Added in 7.29.0)
     *
     * @var int
     */
    public $primary_port;

    /**
     * The IP address of the local end of the most recently done connection.
     * Can be either IPv4 or IPv6. (Added in 7.29.0)
     *
     * @var string
     */
    public $local_ip;

    /**
     * The local port number of the most recently done connection.
     * (Added in 7.29.0)
     *
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
        $stats->appconnect_time = \curl_getinfo($multiInfo['handle'], CURLINFO_APPCONNECT_TIME);

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
     *
     * This time includes the remote server processing time plus a full round trip
     * to the server and back.
     */
    public function waitingTime(): float
    {
        return \max(0.0, $this->starttransfer_time - $this->pretransfer_time);
    }

    /**
     * The elapsed time (in seconds) from the moment cURL received the first
     * byte of the response until the response was fully received.
     */
    public function receivingTime(): float
    {
        if (0 < $this->starttransfer_time) {
            return $this->total_time - $this->starttransfer_time;
        }

        return 0.0;
    }
}
