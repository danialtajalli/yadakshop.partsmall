<?php

namespace App\Jobs\Contact;

use App\Events\ContactLeadSubmitted;
use App\Models\ContactLead;
use Illuminate\Support\Facades\Event;

class FinalizeContactLeadJob extends ContactLeadPipelineJob
{
    public function handle(): void
    {
        $lead = $this->loadLead();

        $lead->update([
            'status' => ContactLead::STATUS_COMPLETED,
            'failure_reason' => null,
        ]);

        Event::dispatch(new ContactLeadSubmitted($lead->fresh()));
    }
}
