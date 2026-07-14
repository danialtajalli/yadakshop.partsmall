<?php

namespace App\Services\Contact\Pipeline\Steps;

use App\DataTransferObjects\Contact\ContactLeadContext;
use App\Models\ContactLead;
use App\Services\Contact\Didar\DidarApiClient;
use App\Services\Contact\Didar\DidarResponseParser;
use App\Services\Contact\Pipeline\ContactLeadStepDecorator;
use Illuminate\Support\Facades\Log;

class ResolveDidarProductStep extends ContactLeadStepDecorator
{
    public function __construct(
        private readonly DidarApiClient $client,
        private readonly DidarResponseParser $parser,
        ?\App\Services\Contact\Pipeline\ContactLeadStep $next = null,
    ) {
        parent::__construct($next);
    }

    protected function process(ContactLeadContext $context): void
    {
        $response = $this->client->post('product/search', [
            'Criteria' => new \stdClass(),
            'From' => 0,
            'Limit' => 10,
        ]);

        if (! $response->ok) {
            Log::error('Didar product/search failed.', ['error' => $response->error]);
            $context->fail('didar_product_search_failed');

            return;
        }

        $productId = $this->parser->extractFirstProductId($response->data ?? []);

        if ($productId === null) {
            Log::error('Didar product/search returned no products.');
            $context->fail('didar_product_missing');

            return;
        }

        $context->productId = $productId;
    }
}
