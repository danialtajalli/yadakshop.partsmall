<?php

namespace App\Filament\Resources\PartCategories\Pages;

use App\Filament\Resources\PartCategories\PartCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPartCategories extends ListRecords
{
    protected static string $resource = PartCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
