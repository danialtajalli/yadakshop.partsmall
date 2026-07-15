<?php

namespace App\Filament\Resources\Images\Concerns;

use Filament\Actions\CreateAction;

trait ConfiguresImagesRelationManager
{
    protected function makeCreateImageAction(string $ownerForeignKey, string $label = 'افزودن تصویر'): CreateAction
    {
        return CreateAction::make()
            ->label($label)
            ->modal()
            ->fillForm(fn (): array => [
                $ownerForeignKey => $this->getOwnerRecord()->getKey(),
            ]);
    }
}
