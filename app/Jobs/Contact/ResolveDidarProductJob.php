<?php

namespace App\Jobs\Contact;

use App\Services\Contact\Actions\ResolveDidarProductAction;

class ResolveDidarProductJob extends ContactLeadPipelineJob
{
    public function handle(ResolveDidarProductAction $action): void
    {
        $action->execute($this->loadLead());
    }
}
