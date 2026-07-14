<?php

namespace App\Services\Contact;

use App\DataTransferObjects\Contact\ContactLeadData;
use App\DataTransferObjects\Contact\ContactLeadResult;
use App\Models\ContactLead;

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

        $leadRecord = ContactLead::query()->create([
            'first_name' => $lead->firstName,
            'last_name' => $lead->lastName,
            'phone' => $lead->phone,
            'message' => $lead->message,
            'status' => ContactLead::STATUS_PENDING,
            'pipeline' => $pipeline,
        ]);

        $this->pipelineFactory->dispatch($leadRecord);

        return ContactLeadResult::success(
            (string) config('contact.messages.success'),
            $leadRecord,
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
}
