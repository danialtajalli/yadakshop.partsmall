<?php

namespace App\Jobs\Contact;

use App\Services\Contact\Actions\ResolveDidarPipelineAction;

class ResolveDidarPipelineJob extends ContactLeadPipelineJob
{
    public function handle(ResolveDidarPipelineAction $action): void
    {
        $action->execute($this->loadLead());
    }
}
