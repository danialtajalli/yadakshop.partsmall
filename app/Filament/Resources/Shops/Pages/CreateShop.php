<?php

namespace App\Filament\Resources\Shops\Pages;

use App\Filament\Resources\Shops\ShopResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShop extends CreateRecord
{
    protected static ?string $title = 'ایجاد فروشگاه جدید';
    protected static string $resource = ShopResource::class;
}
