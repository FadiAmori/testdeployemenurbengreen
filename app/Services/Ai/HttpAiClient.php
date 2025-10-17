<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;

final class HttpAiClient implements AiClientInterface
{
    private readonly string $baseUrl;
    private readonly string $apiKey;
    private readonly HttpFactory $http;

    public function __construct(HttpFactory $http)
    {
        $this->http = $http;
        $this->baseUrl = (string) config('ai.base_url');
        $this->apiKey = (string) config('ai.api_key');
    }

    /**
     * @param array<string,mixed> $payload
     * @param array{timeout?:float} $options
     *
     * @return array<string,mixed>
     */
    public function request(string $endpoint, array $payload, array $options = []): array
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');
        $timeout = (float) ($options['timeout'] ?? config('ai.timeout', 15));

        $response = $this->http
            ->timeout($timeout)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->post($url, $payload);

        try {
            $response->throw();
        } catch (RequestException $exception) {
            Log::error('AI HTTP client request failed', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        /** @var array<string,mixed> $decoded */
        $decoded = $response->json();

        return $decoded;
    }
}
