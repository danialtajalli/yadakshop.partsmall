<?php

namespace App\Services\Contact\Pipeline;

use App\DataTransferObjects\Contact\ContactLeadContext;

interface ContactLeadStep
{
    public function handle(ContactLeadContext $context): void;
}
