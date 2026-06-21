<?php

namespace App\Filament\Resources\PartCategories\Pages;

use App\Filament\Resources\PartCategories\PartCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePartCategory extends CreateRecord
{
    protected static ?string $title = 'ایجاد دسته بندی قطعات جدید';
    protected static string $resource = PartCategoryResource::class;
}
