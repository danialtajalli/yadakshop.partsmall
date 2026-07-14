<?php

namespace App\Jobs\Contact;

use App\Exceptions\ContactLeadPipelineException;
use App\Models\ContactLead;

class ContactLeadFailureHandler
{
    public static function markFailed(int $contactLeadId, ?\Throwable $exception): void
    {
        $lead = ContactLead::query()->find($contactLeadId);

        if ($lead === null) {
            return;
        }

        $reason = $exception instanceof ContactLeadPipelineException
            ? $exception->reason
            : ($exception?->getMessage() ?? 'unknown');

        $lead->update([
            'status' => ContactLead::STATUS_FAILED,
            'failure_reason' => $reason,
        ]);
    }
}
