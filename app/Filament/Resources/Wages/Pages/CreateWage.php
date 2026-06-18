<?php

namespace App\Filament\Resources\Wages\Pages;

use App\Filament\Resources\Wages\WageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWage extends CreateRecord
{
    protected static ?string $title = 'ایجاد اجرت جدید';
    protected static string $resource = WageResource::class;
}
