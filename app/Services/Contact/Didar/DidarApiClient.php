<?php

namespace App\Services\Contact\Didar;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DidarApiClient
{
    public function post(string $endpoint, array|object $body): DidarApiResponse
    {
        $apiKey = (string) config('contact.didar.api_key');

        if ($apiKey === '') {
            return DidarApiResponse::failure(0, null, 'missing_api_key');
        }

        $url = rtrim((string) config('contact.didar.base_url'), '/').'/'.ltrim($endpoint, '/');

        try {
            $response = $this->request()
                ->post($url, $this->normalizeBody($body));
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
            ->asJson()
            ->timeout(30)
            ->withQueryParameters([
                'apikey' => (string) config('contact.didar.api_key'),
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeBody(array|object $body): array
    {
        return json_decode(json_encode($body, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }

    private function toApiResponse(string $endpoint, Response $response): DidarApiResponse
    {
        $decoded = $response->json();

        if (! is_array($decoded)) {
            Log::error('Didar API returned invalid JSON.', ['endpoint' => $endpoint]);

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
