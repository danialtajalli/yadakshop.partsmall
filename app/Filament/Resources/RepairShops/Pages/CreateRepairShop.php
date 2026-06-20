<?php

namespace App\Filament\Resources\RepairShops\Pages;

use App\Filament\Resources\RepairShops\RepairShopResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRepairShop extends CreateRecord
{
    protected static ?string $title = 'افزودن موقعیت سفارشی سازی';
    protected static string $resource = RepairShopResource::class;
}
