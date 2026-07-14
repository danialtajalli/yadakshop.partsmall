<?php

namespace App\Services\Contact\Didar;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DidarApiClient
{
    public function __construct(
        private readonly DidarHttpSsl $ssl,
    ) {}

    public function post(string $endpoint, array|object $body): DidarApiResponse
    {
        $apiKey = (string) config('contact.didar.api_key');

        if ($apiKey === '') {
            return DidarApiResponse::failure(0, null, 'missing_api_key');
        }

        $url = rtrim((string) config('contact.didar.base_url'), '/').'/'.ltrim($endpoint, '/');

        try {
            // Encode ourselves so empty stdClass values stay `{}` (Didar/.NET rejects `[]` for Criteria).
            $response = $this->request()
                ->withBody($this->encodeBody($body), 'application/json')
                ->post($url);
        } catch (\Throwable $exception) {
            Log::error('Didar API request failed.', [
                'endpoint' => $endpoint,
                'message' => $exception->getMessage(),
            ]);

            return DidarApiResponse::failure(0, null, $exception->getMessage());
        }

        return $this->toApiResponse($endpoint, $response);
    }

    private function request(): PendingRequest
    {
        return Http::acceptJson()
            ->timeout(30)
            ->withOptions([
                'verify' => $this->ssl->verifyOption(),
            ])
            ->withQueryParameters([
                'apikey' => (string) config('contact.didar.api_key'),
            ]);
    }

    private function encodeBody(array|object $body): string
    {
        return json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    private function toApiResponse(string $endpoint, Response $response): DidarApiResponse
    {
        $decoded = $response->json();
        $body = $response->body();

        if (! is_array($decoded)) {
            Log::error('Didar API returned a non-JSON response.', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $body === '' ? null : mb_substr($body, 0, 500),
            ]);

            if ($response->status() === 401) {
                return DidarApiResponse::failure(401, null, 'didar_unauthorized');
            }

            return DidarApiResponse::failure($response->status(), null, 'invalid_json');
        }

        if (! $response->successful()) {
            Log::error('Didar API returned an error response.', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $decoded,
            ]);

            return DidarApiResponse::failure($response->status(), $decoded, 'http_'.$response->status());
        }

        return DidarApiResponse::success($response->status(), $decoded);
    }
}
