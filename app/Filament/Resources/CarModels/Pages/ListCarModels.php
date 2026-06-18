<?php

namespace App\Filament\Resources\CarModels\Pages;

use App\Filament\Resources\CarModels\CarModelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCarModels extends ListRecords
{
    protected static ?string $title = 'مدل ها';

    protected static string $resource = CarModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('ایجاد مدل جدید'),
        ];
    }
}
