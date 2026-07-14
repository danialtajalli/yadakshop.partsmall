<?php

namespace App\Jobs\Contact;

use App\Services\Contact\Actions\FindOrCreateDidarContactAction;

class FindOrCreateDidarContactJob extends ContactLeadPipelineJob
{
    public function handle(FindOrCreateDidarContactAction $action): void
    {
        $action->execute($this->loadLead());
    }
}
