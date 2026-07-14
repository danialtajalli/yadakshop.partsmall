<?php

namespace App\Jobs\Contact;

use App\Services\Contact\Actions\ResolveDidarOwnerAction;

class ResolveDidarOwnerJob extends ContactLeadPipelineJob
{
    public function handle(ResolveDidarOwnerAction $action): void
    {
        $action->execute($this->loadLead());
    }
}
