<?php

namespace App\Jobs\Contact;

use App\Models\ContactLead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class ContactLeadPipelineJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [30, 120, 300];

    public function __construct(
        public int $contactLeadId,
    ) {}

    protected function loadLead(): ContactLead
    {
        return ContactLead::query()->findOrFail($this->contactLeadId);
    }

    public function failed(?\Throwable $exception): void
    {
        ContactLeadFailureHandler::markFailed($this->contactLeadId, $exception);
    }
}
