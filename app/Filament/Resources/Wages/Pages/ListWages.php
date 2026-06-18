<?php

namespace App\Filament\Resources\Wages\Pages;

use App\Filament\Resources\Wages\WageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWages extends ListRecords
{
    protected static string $resource = WageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('ایجاد اجرت جدید'),
        ];
    }
}
