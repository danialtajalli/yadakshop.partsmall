<?php

namespace App\Services\Contact;

use App\DataTransferObjects\Contact\ContactLeadContext;
use App\DataTransferObjects\Contact\ContactLeadData;
use App\DataTransferObjects\Contact\ContactLeadResult;
use App\Events\ContactLeadSubmitted;
use App\Models\ContactLead;
use Illuminate\Support\Facades\Event;

class ContactLeadSubmissionService
{
    public function __construct(
        private readonly ContactLeadPipelineFactory $pipelineFactory,
    ) {}

    public function submit(ContactLeadData $lead, ?string $pipeline = null): ContactLeadResult
    {
        $pipeline ??= (string) config('contact.pipeline');

        if ($this->requiresDidar($pipeline) && ! $this->didarIsConfigured()) {
            return ContactLeadResult::failure((string) config('contact.messages.misconfigured'));
        }

        $context = new ContactLeadContext($lead, $pipeline);
        $head = $this->pipelineFactory->make($pipeline);

        $head->handle($context);
        $this->finalizeLeadRecord($context);

        if ($context->failed) {
            return ContactLeadResult::failure(
                (string) config('contact.messages.failure'),
                $context->leadRecord,
            );
        }

        Event::dispatch(new ContactLeadSubmitted($lead, $context));

        return ContactLeadResult::success(
            (string) config('contact.messages.success'),
            $context->leadRecord,
        );
    }

    private function requiresDidar(string $pipeline): bool
    {
        return in_array($pipeline, ['didar', 'didar_with_database'], true);
    }

    private function didarIsConfigured(): bool
    {
        return filled(config('contact.didar.api_key'))
            && filled(config('contact.didar.owner_username'));
    }

    private function finalizeLeadRecord(ContactLeadContext $context): void
    {
        if ($context->leadRecord === null) {
            return;
        }

        if ($context->failed) {
            $context->leadRecord->update([
                'status' => ContactLead::STATUS_FAILED,
                'failure_reason' => $context->failureReason,
                'didar_product_id' => $context->productId,
                'didar_person_id' => $context->personId,
                'didar_deal_id' => $context->dealId,
            ]);

            return;
        }

        $context->leadRecord->update([
            'status' => ContactLead::STATUS_COMPLETED,
            'didar_product_id' => $context->productId,
            'didar_person_id' => $context->personId,
            'didar_deal_id' => $context->dealId,
            'failure_reason' => null,
        ]);
    }
}
