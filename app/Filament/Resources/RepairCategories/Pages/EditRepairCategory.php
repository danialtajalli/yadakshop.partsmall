<?php

namespace App\Filament\Resources\RepairCategories\Pages;

use App\Filament\Resources\RepairCategories\RepairCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRepairCategory extends EditRecord
{
    protected static ?string $title = 'ویرایش دسته بندی تعمیرگاه';
    protected static string $resource = RepairCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
