<?php

namespace App\Services\Contact\Pipeline\Steps;

use App\Models\ContactLead;
use App\Services\Contact\Pipeline\ContactLeadStep;
use App\Services\Contact\Pipeline\ContactLeadStepDecorator;

class PersistContactLeadStep extends ContactLeadStepDecorator
{
    public function __construct(?ContactLeadStep $next = null)
    {
        parent::__construct($next);
    }

    protected function process(\App\DataTransferObjects\Contact\ContactLeadContext $context): void
    {
        $context->leadRecord = ContactLead::query()->create([
            'first_name' => $context->lead->firstName,
            'last_name' => $context->lead->lastName,
            'phone' => $context->lead->phone,
            'message' => $context->lead->message,
            'status' => ContactLead::STATUS_PENDING,
            'pipeline' => $context->pipeline,
        ]);
    }
}
