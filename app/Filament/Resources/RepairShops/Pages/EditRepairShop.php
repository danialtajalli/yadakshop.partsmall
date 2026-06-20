<?php

namespace App\Filament\Resources\RepairShops\Pages;

use App\Filament\Resources\RepairShops\RepairShopResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRepairShop extends EditRecord
{
    protected static ?string $title = 'ویرایش موقعیت سفارشی سازی';
    protected static string $resource = RepairShopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('حذف'),
        ];
    }
}
