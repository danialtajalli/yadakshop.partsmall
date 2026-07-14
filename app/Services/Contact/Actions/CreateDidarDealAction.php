<?php

namespace App\Services\Contact\Actions;

use App\Exceptions\ContactLeadPipelineException;
use App\Models\ContactLead;
use App\Services\Contact\Didar\DidarApiClient;
use App\Services\Contact\Didar\DidarResponseParser;
use Illuminate\Support\Facades\Log;

class CreateDidarDealAction
{
    public function __construct(
        private readonly DidarApiClient $client,
        private readonly DidarResponseParser $parser,
    ) {}

    public function execute(ContactLead $lead): void
    {
        $dealFieldKey = (string) config('contact.didar.deal_field_key');
        $fullName = trim($lead->first_name.' '.$lead->last_name);
        $dealTitle = 'معامله تماس — '.$fullName;
        $dealDescription = $lead->message !== ''
            ? $lead->message
            : 'درخواست تماس از وب‌سایت';

        $response = $this->client->post('deal/save', [
            'Deal' => [
                'PersonId' => $lead->didar_person_id,
                'Title' => $dealTitle,
                'OwnerId' => $lead->didar_owner_id,
                'PipelineStageId' => $lead->didar_pipeline_stage_id,
                'SegmentIds' => [],
                'Description' => $dealDescription,
                'VisibilityType' => 'All',
                'Fields' => [
                    $dealFieldKey => $lead->message !== '' ? $lead->message : $dealDescription,
                ],
            ],
            'DealItems' => [
                [
                    'Quantity' => 1,
                    'ProductId' => $lead->didar_product_id,
                ],
            ],
            'IsWon' => false,
            'WelcomeQuoteId' => null,
        ]);

        if (! $response->ok) {
            Log::error('Didar deal/save failed.', ['error' => $response->error]);

            throw new ContactLeadPipelineException('didar_deal_save_failed');
        }

        $dealId = $this->parser->extractDealId($response->data ?? []);

        if ($dealId !== null) {
            $lead->update(['didar_deal_id' => $dealId]);
        }
    }
}
