<?php

namespace App\Filament\Resources\Shops\Pages;

use App\Filament\Resources\Shops\ShopResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShops extends ListRecords
{
    protected static ?string $title = 'فروشگاه ها';
    protected static string $resource = ShopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('ایجاد فروشگاه جدید'),
        ];
    }
}
