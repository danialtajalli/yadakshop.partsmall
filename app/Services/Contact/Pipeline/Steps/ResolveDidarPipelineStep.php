<?php

namespace App\Services\Contact\Pipeline\Steps;

use App\DataTransferObjects\Contact\ContactLeadContext;
use App\Services\Contact\Didar\DidarApiClient;
use App\Services\Contact\Didar\DidarResponseParser;
use App\Services\Contact\Pipeline\ContactLeadStepDecorator;
use Illuminate\Support\Facades\Log;

class ResolveDidarPipelineStep extends ContactLeadStepDecorator
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
        $response = $this->client->post('pipeline/list/0', new \stdClass());

        if (! $response->ok) {
            Log::error('Didar pipeline/list/0 failed.', ['error' => $response->error]);
            $context->fail('didar_pipeline_list_failed');

            return;
        }

        $pipeline = $this->parser->extractFirstPipelineStage($response->data ?? []);

        if ($pipeline['pipeline_stage_id'] === null) {
            Log::error('Didar pipeline/list/0 returned no stage id.');
            $context->fail('didar_pipeline_stage_missing');

            return;
        }

        $context->pipelineStageId = $pipeline['pipeline_stage_id'];
        $context->pipelineId = $pipeline['pipeline_id'];
    }
}
