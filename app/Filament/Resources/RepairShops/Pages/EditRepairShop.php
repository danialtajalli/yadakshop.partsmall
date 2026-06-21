<?php

namespace App\Filament\Resources\RepairShops\Pages;

use App\Filament\Resources\RepairShops\RepairShopResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Livewire\Attributes\On;

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

    #[On('location-updated')]
    public function updateLocation($latitude, $longitude): void
    {
        $this->data['latitude'] = $latitude;
        $this->data['longitude'] = $longitude;
    }
}
