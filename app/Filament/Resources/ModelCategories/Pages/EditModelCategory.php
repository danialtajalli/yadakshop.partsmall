<?php

namespace App\Filament\Resources\ModelCategories\Pages;

use App\Filament\Resources\ModelCategories\ModelCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditModelCategory extends EditRecord
{
    protected static ?string $title = 'ویرایش دسته بندی مدل ها';
    protected static string $resource = ModelCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('حذف'),
        ];
    }
}
