<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCompany extends CreateRecord
{
    protected static ?string $title = 'افزودن کمپانی';
    protected static string $resource = CompanyResource::class;
}
