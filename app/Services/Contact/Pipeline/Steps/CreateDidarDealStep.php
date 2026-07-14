<?php

namespace App\Services\Contact\Pipeline\Steps;

use App\DataTransferObjects\Contact\ContactLeadContext;
use App\Services\Contact\Didar\DidarApiClient;
use App\Services\Contact\Didar\DidarResponseParser;
use App\Services\Contact\Pipeline\ContactLeadStepDecorator;
use Illuminate\Support\Facades\Log;

class CreateDidarDealStep extends ContactLeadStepDecorator
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
        $dealFieldKey = (string) config('contact.didar.deal_field_key');
        $dealTitle = 'معامله تماس — '.$context->lead->fullName();
        $dealDescription = $context->lead->message !== ''
            ? $context->lead->message
            : 'درخواست تماس از وب‌سایت';

        $response = $this->client->post('deal/save', [
            'Deal' => [
                'PersonId' => $context->personId,
                'Title' => $dealTitle,
                'OwnerId' => $context->ownerId,
                'PipelineStageId' => $context->pipelineStageId,
                'SegmentIds' => [],
                'Description' => $dealDescription,
                'VisibilityType' => 'All',
                'Fields' => [
                    $dealFieldKey => $context->lead->message !== '' ? $context->lead->message : $dealDescription,
                ],
            ],
            'DealItems' => [
                [
                    'Quantity' => 1,
                    'ProductId' => $context->productId,
                ],
            ],
            'IsWon' => false,
            'WelcomeQuoteId' => null,
        ]);

        if (! $response->ok) {
            Log::error('Didar deal/save failed.', ['error' => $response->error]);
            $context->fail('didar_deal_save_failed');

            return;
        }

        $dealId = $this->parser->extractDealId($response->data ?? []);

        if ($dealId !== null) {
            $context->dealId = $dealId;
        }
    }
}
