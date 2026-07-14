<?php

namespace App\Services\Contact\Pipeline\Steps;

use App\DataTransferObjects\Contact\ContactLeadContext;
use App\Services\Contact\Didar\DidarApiClient;
use App\Services\Contact\Didar\DidarResponseParser;
use App\Services\Contact\Pipeline\ContactLeadStepDecorator;
use Illuminate\Support\Facades\Log;

class ResolveDidarOwnerStep extends ContactLeadStepDecorator
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
        $ownerUsername = (string) config('contact.didar.owner_username');

        if ($ownerUsername === '') {
            $context->fail('didar_owner_misconfigured');

            return;
        }

        $response = $this->client->post('User/List', new \stdClass());

        if (! $response->ok) {
            Log::error('Didar User/List failed.', ['error' => $response->error]);
            $context->fail('didar_user_list_failed');

            return;
        }

        $ownerId = $this->parser->extractOwnerUserId($response->data ?? [], $ownerUsername);

        if ($ownerId === null) {
            Log::error('Didar User/List did not contain owner username.', ['username' => $ownerUsername]);
            $context->fail('didar_owner_missing');

            return;
        }

        $context->ownerId = $ownerId;
    }
}
