<?php

namespace App\Filament\Resources\Representations\Pages;

use App\Filament\Resources\Representations\RepresentationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRepresentation extends EditRecord
{
    protected static string $resource = RepresentationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
