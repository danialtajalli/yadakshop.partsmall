<?php

namespace App\Services\Contact;

use App\Jobs\Contact\ContactLeadFailureHandler;
use App\Jobs\Contact\CreateDidarDealJob;
use App\Jobs\Contact\FinalizeContactLeadJob;
use App\Jobs\Contact\FindOrCreateDidarContactJob;
use App\Jobs\Contact\ResolveDidarOwnerJob;
use App\Jobs\Contact\ResolveDidarPipelineJob;
use App\Jobs\Contact\ResolveDidarProductJob;
use App\Models\ContactLead;
use Illuminate\Support\Facades\Bus;
use InvalidArgumentException;
use Throwable;

class ContactLeadPipelineFactory
{
    public function dispatch(ContactLead $lead): void
    {
        $jobs = match ($lead->pipeline) {
            'database_only' => [
                new FinalizeContactLeadJob($lead->id),
            ],
            'didar', 'didar_with_database' => $this->didarJobs($lead->id),
            default => throw new InvalidArgumentException("Unsupported contact pipeline [{$lead->pipeline}]."),
        };

        Bus::chain($jobs)
            ->catch(function (Throwable $exception) use ($lead): void {
                ContactLeadFailureHandler::markFailed($lead->id, $exception);
            })
            ->dispatch();
    }

    /**
     * @return list<object>
     */
    private function didarJobs(int $contactLeadId): array
    {
        return [
            new ResolveDidarProductJob($contactLeadId),
            new FindOrCreateDidarContactJob($contactLeadId),
            new ResolveDidarOwnerJob($contactLeadId),
            new ResolveDidarPipelineJob($contactLeadId),
            new CreateDidarDealJob($contactLeadId),
            new FinalizeContactLeadJob($contactLeadId),
        ];
    }
}
