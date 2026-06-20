<?php

namespace App\Filament\Resources\Phones\Pages;

use App\Filament\Resources\Phones\PhoneResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPhones extends ListRecords
{
    protected static ?string $title = 'تلفن ها';
    protected static string $resource = PhoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('افزودن تلفن'),
        ];
    }
}
