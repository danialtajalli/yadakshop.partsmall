<?php

namespace App\Services\Contact\Pipeline;

use App\DataTransferObjects\Contact\ContactLeadContext;

abstract class ContactLeadStepDecorator implements ContactLeadStep
{
    public function __construct(
        protected ?ContactLeadStep $next = null,
    ) {}

    public function handle(ContactLeadContext $context): void
    {
        if (! $context->shouldContinue()) {
            return;
        }

        $this->process($context);

        if ($context->shouldContinue()) {
            $this->next?->handle($context);
        }
    }

    abstract protected function process(ContactLeadContext $context): void;
}
