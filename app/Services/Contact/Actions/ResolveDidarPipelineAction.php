<?php

namespace App\Services\Contact\Actions;

use App\Exceptions\ContactLeadPipelineException;
use App\Models\ContactLead;
use App\Services\Contact\Didar\DidarApiClient;
use App\Services\Contact\Didar\DidarResponseParser;
use Illuminate\Support\Facades\Log;

class ResolveDidarPipelineAction
{
    public function __construct(
        private readonly DidarApiClient $client,
        private readonly DidarResponseParser $parser,
    ) {}

    public function execute(ContactLead $lead): void
    {
        $response = $this->client->post('pipeline/list/0', new \stdClass());

        if (! $response->ok) {
            Log::error('Didar pipeline/list/0 failed.', ['error' => $response->error]);

            throw new ContactLeadPipelineException('didar_pipeline_list_failed');
        }

        $pipeline = $this->parser->extractFirstPipelineStage($response->data ?? []);

        if ($pipeline['pipeline_stage_id'] === null) {
            Log::error('Didar pipeline/list/0 returned no stage id.');

            throw new ContactLeadPipelineException('didar_pipeline_stage_missing');
        }

        $lead->update([
            'didar_pipeline_id' => $pipeline['pipeline_id'],
            'didar_pipeline_stage_id' => $pipeline['pipeline_stage_id'],
        ]);
    }
}
