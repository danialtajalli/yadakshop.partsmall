<?php

namespace App\Events;

use App\Models\ContactLead;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContactLeadSubmitted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ContactLead $lead,
    ) {}
}
