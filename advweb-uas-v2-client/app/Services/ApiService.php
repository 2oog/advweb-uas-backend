<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Session;

class ApiService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => rtrim(config('api.url'), '/') . '/',
            'timeout' => config('api.timeout', 30),
            'http_errors' => false,
        ]);
    }

    /**
     * Get the authentication token from session
     */
    protected function getToken(): ?string
    {
        return Session::get('api_token');
    }

    /**
     * Set the authentication token in session
     */
    public function setToken(string $token): void
    {
        Session::put('api_token', $token);
    }

    /**
     * Clear the authentication token from session
     */
    public function clearToken(): void
    {
        Session::forget('api_token');
        Session::forget('user');
    }

    /**
     * Get default headers for requests
     */
    protected function getHeaders(bool $authenticated = true): array
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if ($authenticated && $this->getToken()) {
            $headers['Authorization'] = 'Bearer ' . $this->getToken();
        }

        return $headers;
    }

    /**
     * Make a GET request
     */
    public function get(string $endpoint, array $query = [], bool $authenticated = true): array
    {
        try {
            $response = $this->client->get($endpoint, [
                'headers' => $this->getHeaders($authenticated),
                'query' => $query,
            ]);

            return $this->handleResponse($response);
        } catch (ConnectException $e) {
            return [
                'success' => false,
                'error' => 'Could not connect to API server',
                'status' => 0,
            ];
        }
    }

    /**
     * Make a POST request
     */
    public function post(string $endpoint, array $data = [], bool $authenticated = true): array
    {
        try {
            $response = $this->client->post($endpoint, [
                'headers' => $this->getHeaders($authenticated),
                'json' => $data,
            ]);

            return $this->handleResponse($response);
        } catch (ConnectException $e) {
            return [
                'success' => false,
                'error' => 'Could not connect to API server',
                'status' => 0,
            ];
        }
    }

    /**
     * Make a POST request with multipart form data (for file uploads)
     */
    public function postMultipart(string $endpoint, array $multipart, bool $authenticated = true): array
    {
        try {
            $headers = $this->getHeaders($authenticated);
            unset($headers['Content-Type']); // Let Guzzle set it for multipart

            $response = $this->client->post($endpoint, [
                'headers' => $headers,
                'multipart' => $multipart,
            ]);

            return $this->handleResponse($response);
        } catch (ConnectException $e) {
            return [
                'success' => false,
                'error' => 'Could not connect to API server',
                'status' => 0,
            ];
        }
    }

    /**
     * Make a PUT request
     */
    public function put(string $endpoint, array $data = [], bool $authenticated = true): array
    {
        try {
            $response = $this->client->put($endpoint, [
                'headers' => $this->getHeaders($authenticated),
                'json' => $data,
            ]);

            return $this->handleResponse($response);
        } catch (ConnectException $e) {
            return [
                'success' => false,
                'error' => 'Could not connect to API server',
                'status' => 0,
            ];
        }
    }

    /**
     * Make a DELETE request
     */
    public function delete(string $endpoint, bool $authenticated = true): array
    {
        try {
            $response = $this->client->delete($endpoint, [
                'headers' => $this->getHeaders($authenticated),
            ]);

            return $this->handleResponse($response);
        } catch (ConnectException $e) {
            return [
                'success' => false,
                'error' => 'Could not connect to API server',
                'status' => 0,
            ];
        }
    }

    /**
     * Handle API response
     */
    protected function handleResponse($response): array
    {
        $statusCode = $response->getStatusCode();
        $body = json_decode($response->getBody()->getContents(), true) ?? [];

        if ($statusCode >= 200 && $statusCode < 300) {
            return [
                'success' => true,
                'data' => $body,
                'status' => $statusCode,
            ];
        }

        return [
            'success' => false,
            'error' => $body['message'] ?? 'An error occurred',
            'errors' => $body['errors'] ?? [],
            'status' => $statusCode,
        ];
    }
}
