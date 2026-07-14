<?php

namespace App\Services\Contact\Actions;

use App\Exceptions\ContactLeadPipelineException;
use App\Models\ContactLead;
use App\Services\Contact\Didar\DidarApiClient;
use App\Services\Contact\Didar\DidarResponseParser;
use Illuminate\Support\Facades\Log;

class FindOrCreateDidarContactAction
{
    public function __construct(
        private readonly DidarApiClient $client,
        private readonly DidarResponseParser $parser,
    ) {}

    public function execute(ContactLead $lead): void
    {
        if ($lead->didar_person_id !== null) {
            return;
        }

        $existingId = $this->findExistingContact($lead);

        if ($existingId !== null) {
            $lead->update(['didar_person_id' => $existingId]);

            return;
        }

        $this->createContact($lead);
    }

    private function findExistingContact(ContactLead $lead): ?string
    {
        $response = $this->client->post('contact/search', [
            'Criteria' => [
                'WorkPhone' => $lead->phone,
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

    private function createContact(ContactLead $lead): void
    {
        $response = $this->client->post('contact/save', [
            'Contact' => [
                'VisibilityType' => 'OwnerGroup',
                'FirstName' => $lead->first_name,
                'LastName' => $lead->last_name,
                'WorkPhone' => $lead->phone,
                'Fields' => new \stdClass(),
                'Type' => 'Person',
            ],
        ]);

        if (! $response->ok) {
            Log::error('Didar contact/save failed.', ['error' => $response->error]);

            throw new ContactLeadPipelineException('didar_contact_save_failed');
        }

        $personId = $this->parser->extractPersonId($response->data ?? []);

        if ($personId === null) {
            Log::error('Didar contact/save returned no person id.');

            throw new ContactLeadPipelineException('didar_contact_missing_id');
        }

        $lead->update(['didar_person_id' => $personId]);
    }
}
