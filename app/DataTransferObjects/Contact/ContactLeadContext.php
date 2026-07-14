<?php

namespace App\DataTransferObjects\Contact;

use App\Models\ContactLead;

class ContactLeadContext
{
    public ?string $productId = null;

    public ?string $personId = null;

    public ?string $ownerId = null;

    public ?string $pipelineStageId = null;

    public ?string $pipelineId = null;

    public ?string $dealId = null;

    public ?ContactLead $leadRecord = null;

    public bool $failed = false;

    public ?string $failureReason = null;

    public function __construct(
        public readonly ContactLeadData $lead,
        public readonly string $pipeline,
    ) {}

    public function fail(string $reason): void
    {
        $this->failed = true;
        $this->failureReason = $reason;
    }

    public function shouldContinue(): bool
    {
        return ! $this->failed;
    }
}
