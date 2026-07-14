<?php

namespace App\Services\Contact\Pipeline\Steps;

use App\DataTransferObjects\Contact\ContactLeadContext;
use App\Services\Contact\Didar\DidarApiClient;
use App\Services\Contact\Didar\DidarResponseParser;
use App\Services\Contact\Pipeline\ContactLeadStepDecorator;
use Illuminate\Support\Facades\Log;

class FindOrCreateDidarContactStep extends ContactLeadStepDecorator
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
        $existingId = $this->findExistingContact($context);

        if ($existingId !== null) {
            $context->personId = $existingId;

            return;
        }

        $this->createContact($context);
    }

    private function findExistingContact(ContactLeadContext $context): ?string
    {
        $response = $this->client->post('contact/search', [
            'Criteria' => [
                'WorkPhone' => $context->lead->phone,
            ],
            'From' => 0,
            'Limit' => 1,
        ]);

        if (! $response->ok) {
            Log::warning('Didar contact/search failed; falling back to contact/save.', [
                'error' => $response->error,
            ]);

            return null;
        }

        return $this->parser->extractFirstContactIdFromSearch($response->data ?? []);
    }

    private function createContact(ContactLeadContext $context): void
    {
        $response = $this->client->post('contact/save', [
            'Contact' => [
                'VisibilityType' => 'OwnerGroup',
                'FirstName' => $context->lead->firstName,
                'LastName' => $context->lead->lastName,
                'WorkPhone' => $context->lead->phone,
                'Fields' => new \stdClass(),
                'Type' => 'Person',
            ],
        ]);

        if (! $response->ok) {
            Log::error('Didar contact/save failed.', ['error' => $response->error]);
            $context->fail('didar_contact_save_failed');

            return;
        }

        $personId = $this->parser->extractPersonId($response->data ?? []);

        if ($personId === null) {
            Log::error('Didar contact/save returned no person id.');
            $context->fail('didar_contact_missing_id');

            return;
        }

        $context->personId = $personId;
    }
}
