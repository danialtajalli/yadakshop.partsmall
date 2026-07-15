<?php

namespace App\Filament\Concerns;

use Filament\Actions\CreateAction;

trait ConfiguresModalRelationCreate
{
    protected function makeModalCreateAction(string $ownerForeignKey, string $label): CreateAction
    {
        return CreateAction::make()
            ->label($label)
            ->modal()
            ->fillForm(fn (): array => [
                $ownerForeignKey => $this->getOwnerRecord()->getKey(),
            ]);
    }
}
