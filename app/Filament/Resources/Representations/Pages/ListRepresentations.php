<?php

namespace App\Filament\Resources\Representations\Pages;

use App\Filament\Resources\Representations\RepresentationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRepresentations extends ListRecords
{
    protected static string $resource = RepresentationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
