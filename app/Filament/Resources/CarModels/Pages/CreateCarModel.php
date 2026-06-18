<?php

namespace App\Filament\Resources\CarModels\Pages;

use App\Filament\Resources\CarModels\CarModelResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCarModel extends CreateRecord
{
    protected static ?string $title = 'ایجاد مدل جدید';
    protected static string $resource = CarModelResource::class;
}
