<?php

namespace App\Filament\Resources\RepairCategories\Pages;

use App\Filament\Resources\RepairCategories\RepairCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRepairCategory extends CreateRecord
{
    protected static ?string $title = 'ایجاد دسته بندی تعمیرگاه جدید';
    protected static string $resource = RepairCategoryResource::class;
}
