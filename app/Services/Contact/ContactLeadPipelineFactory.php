<?php

namespace App\Services\Contact;

use App\Services\Contact\Pipeline\ContactLeadStep;
use App\Services\Contact\Pipeline\Steps\CreateDidarDealStep;
use App\Services\Contact\Pipeline\Steps\FindOrCreateDidarContactStep;
use App\Services\Contact\Pipeline\Steps\PersistContactLeadStep;
use App\Services\Contact\Pipeline\Steps\ResolveDidarOwnerStep;
use App\Services\Contact\Pipeline\Steps\ResolveDidarPipelineStep;
use App\Services\Contact\Pipeline\Steps\ResolveDidarProductStep;
use InvalidArgumentException;

class ContactLeadPipelineFactory
{
    public function make(?string $pipeline = null): ContactLeadStep
    {
        $pipeline ??= (string) config('contact.pipeline');

        return match ($pipeline) {
            'database_only' => app(PersistContactLeadStep::class, ['next' => null]),
            'didar' => $this->didarChain(null),
            'didar_with_database' => app(PersistContactLeadStep::class, [
                'next' => $this->didarChain(null),
            ]),
            default => throw new InvalidArgumentException("Unsupported contact pipeline [{$pipeline}]."),
        };
    }

    private function didarChain(?ContactLeadStep $tail): ContactLeadStep
    {
        return app(ResolveDidarProductStep::class, [
            'next' => app(FindOrCreateDidarContactStep::class, [
                'next' => app(ResolveDidarOwnerStep::class, [
                    'next' => app(ResolveDidarPipelineStep::class, [
                        'next' => app(CreateDidarDealStep::class, ['next' => $tail]),
                    ]),
                ]),
            ]),
        ]);
    }
}
