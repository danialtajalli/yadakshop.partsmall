<?php

namespace App\Services\Contact\Actions;

use App\Exceptions\ContactLeadPipelineException;
use App\Models\ContactLead;
use App\Services\Contact\Didar\DidarApiClient;
use App\Services\Contact\Didar\DidarResponseParser;
use Illuminate\Support\Facades\Log;

class ResolveDidarProductAction
{
    public function __construct(
        private readonly DidarApiClient $client,
        private readonly DidarResponseParser $parser,
    ) {}

    public function execute(ContactLead $lead): void
    {
        $response = $this->client->post('product/search', [
            'Criteria' => new \stdClass(),
            'From' => 0,
            'Limit' => 10,
        ]);

        if (! $response->ok) {
            Log::error('Didar product/search failed.', ['error' => $response->error]);

            throw new ContactLeadPipelineException('didar_product_search_failed');
        }

        $productId = $this->parser->extractFirstProductId($response->data ?? []);

        if ($productId === null) {
            Log::error('Didar product/search returned no products.');

            throw new ContactLeadPipelineException('didar_product_missing');
        }

        $lead->update(['didar_product_id' => $productId]);
    }
}
