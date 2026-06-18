<?php

namespace App\Filament\Resources\CarModels\Pages;

use App\Filament\Resources\CarModels\CarModelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCarModel extends EditRecord
{
    protected static ?string $title = 'ویرایش مدل';

    protected static string $resource = CarModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
