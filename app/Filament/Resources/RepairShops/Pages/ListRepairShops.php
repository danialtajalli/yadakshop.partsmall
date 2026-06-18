<?php

namespace App\Filament\Resources\RepairShops\Pages;

use App\Filament\Resources\RepairShops\RepairShopResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRepairShops extends ListRecords
{
    protected static string $resource = RepairShopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
