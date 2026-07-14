<?php

namespace App\Services\Contact\Actions;

use App\Exceptions\ContactLeadPipelineException;
use App\Models\ContactLead;
use App\Services\Contact\Didar\DidarApiClient;
use App\Services\Contact\Didar\DidarResponseParser;
use Illuminate\Support\Facades\Log;

class ResolveDidarOwnerAction
{
    public function __construct(
        private readonly DidarApiClient $client,
        private readonly DidarResponseParser $parser,
    ) {}

    public function execute(ContactLead $lead): void
    {
        $ownerUsername = (string) config('contact.didar.owner_username');

        if ($ownerUsername === '') {
            throw new ContactLeadPipelineException('didar_owner_misconfigured');
        }

        $response = $this->client->post('User/List', new \stdClass());

        if (! $response->ok) {
            Log::error('Didar User/List failed.', ['error' => $response->error]);

            throw new ContactLeadPipelineException('didar_user_list_failed');
        }

        $ownerId = $this->parser->extractOwnerUserId($response->data ?? [], $ownerUsername);

        if ($ownerId === null) {
            Log::error('Didar User/List did not contain owner username.', ['username' => $ownerUsername]);

            throw new ContactLeadPipelineException('didar_owner_missing');
        }

        $lead->update(['didar_owner_id' => $ownerId]);
    }
}
