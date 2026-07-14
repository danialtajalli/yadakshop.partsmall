<?php

namespace App\Jobs\Contact;

use App\Services\Contact\Actions\CreateDidarDealAction;

class CreateDidarDealJob extends ContactLeadPipelineJob
{
    public function handle(CreateDidarDealAction $action): void
    {
        $action->execute($this->loadLead());
    }
}
