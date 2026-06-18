<?php

namespace App\Filament\Resources\RepairCategories\Pages;

use App\Filament\Resources\RepairCategories\RepairCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRepairCategories extends ListRecords
{
    protected static string $resource = RepairCategoryResource::class;

    protected static ?string $title = 'دسته بندی های تعمیرگاه';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('ایجاد دسته بندی تعمیرگاه جدید'),
        ];
    }
}
