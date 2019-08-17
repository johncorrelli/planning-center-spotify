<?php

namespace App\Models;

use App\Exceptions\HttpException;

class Api
{
    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * Initialize any headers that you need to send with every request.
     *
     * @var array
     */
    protected $defaultHeaders;

    /**
     * Sets the POST request content-type.
     *
     * @var string
     */
    protected $postBodyFormat;

    public function __construct(array $defaultHeaders = [])
    {
        $this->defaultHeaders = $defaultHeaders;
        $this->postBodyFormat = 'text/json';
    }

    /**
     * Depending on the $bodyFormat, format the request body accordingly.
     *
     * @param string $bodyFormat
     * @param array $body
     */
    public function formatPostBody(string $bodyFormat, array $body = [])
    {
        $this->defaultHeaders[] = "Content-Type: {$bodyFormat}";

        if ($bodyFormat === 'text/json') {
            return json_encode($body);
        }
        if ($bodyFormat === 'application/x-www-form-urlencoded') {
            return http_build_query($body);
        }

        return $body;
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Trigger your API requests.
     *
     * @param string $method
     * @param string $url
     * @param array $body
     * @param array $additionalHeaders
     *
     * @return object
     */
    public function request(string $method, string $url, array $body = [], array $additionalHeaders = []): object
    {
        $request = curl_init($this->baseUrl.'/'.$url);
        $headers = array_merge($this->defaultHeaders, $additionalHeaders);

        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($request, CURLOPT_ENCODING, 1);

        if ($method === 'POST' || $method === 'PUT') {
            $postBody = $this->formatPostBody($this->postBodyFormat, $body);
            curl_setopt($request, CURLOPT_POST, 1);
            curl_setopt($request, CURLOPT_POSTFIELDS, $postBody);

            if ($method === 'PUT') {
                curl_setopt($request, CURLOPT_CUSTOMREQUEST, 'PUT');
            }
        }

        curl_setopt($request, CURLOPT_HTTPHEADER, $headers);

        $responseBody = curl_exec($request);

        if (is_array($request) && $request['http_code'] >= 400) {
            throw new HttpException();
        }

        return json_decode($responseBody);
    }

    /**
     * Sets the authorization for every API request.
     *
     * @param string $authHeader
     */
    public function setAuthorization(string $authHeader): void
    {
        $this->defaultHeaders[] = $authHeader;
    }

    /**
     * Sets the $baseUrl for every API request.
     *
     * @param string $baseUrl
     */
    public function setBaseUrl(string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * Set the format used to post requests.
     *
     * @param string $format
     */
    public function setPostBodyFormat(string $format): void
    {
        $this->postBodyFormat = $format;
    }
}
