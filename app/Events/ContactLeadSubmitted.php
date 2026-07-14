<?php

namespace App\Events;

use App\DataTransferObjects\Contact\ContactLeadContext;
use App\DataTransferObjects\Contact\ContactLeadData;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContactLeadSubmitted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ContactLeadData $lead,
        public readonly ContactLeadContext $context,
    ) {}
}
