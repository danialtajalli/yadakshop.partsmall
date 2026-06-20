<?php

namespace App\Filament\Resources\ModelCategories\Pages;

use App\Filament\Resources\ModelCategories\ModelCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListModelCategories extends ListRecords
{
    protected static ?string $title = 'لیست دسته بندی مدل ها';
    protected static string $resource = ModelCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('افزودن دسته بندی مدل ها'),
        ];
    }
}
