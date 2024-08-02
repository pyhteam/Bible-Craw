<?php

namespace App\Http;

include("vendor/autoload.php");
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
class HttpClient
{
    protected $client;
    public function __construct()
    {
        $this->client = new Client([
            'allow_redirects' => [
                'max' => 10, // Số lần chuyển hướng tối đa
                'strict' => true,
                'referer' => true,
                'protocols' => ['http', 'https'],
                'track_redirects' => true
            ],
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ]
        ]);
    }

    public function get($url, $options = [])
    {
        try {
            $response = $this->client->request('GET', $url, $options);
            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            return $this->handleException($e);
        }
    }

    public function post($url, $data = [], $options = [])
    {
        try {
            $options['json'] = $data;
            $response = $this->client->request('POST', $url, $options);
            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            return $this->handleException($e);
        }
    }

    public function put($url, $data = [], $options = [])
    {
        try {
            $options['json'] = $data;
            $response = $this->client->request('PUT', $url, $options);
            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            return $this->handleException($e);
        }
    }

    public function delete($url, $options = [])
    {
        try {
            $response = $this->client->request('DELETE', $url, $options);
            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            return $this->handleException($e);
        }
    }

    protected function handleException(RequestException $e)
    {
        if ($e->hasResponse()) {
            return $e->getResponse()->getBody()->getContents();
        }

        return $e->getMessage();
    }
}
