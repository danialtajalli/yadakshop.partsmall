<?php

namespace App\Filament\Resources\ModelCategories\Pages;

use App\Filament\Resources\ModelCategories\ModelCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateModelCategory extends CreateRecord
{
    protected static ?string $title = 'افزودن دسته بندی مدل ها';
    protected static string $resource = ModelCategoryResource::class;
}
