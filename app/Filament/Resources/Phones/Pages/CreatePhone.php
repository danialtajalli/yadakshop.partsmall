<?php

namespace App\Filament\Resources\Phones\Pages;

use App\Filament\Resources\Phones\PhoneResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePhone extends CreateRecord
{
    protected static ?string $title = 'افزودن تلفن';
    protected static string $resource = PhoneResource::class;
}
