<?php

namespace App\Filament\Resources\Shops\Pages;

use App\Filament\Resources\Shops\ShopResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Livewire\Attributes\On;

class EditShop extends EditRecord
{
    protected static ?string $title = 'ویرایش فروشگاه';
    protected static string $resource = ShopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('حذف فروشگاه'),
        ];
    }

    #[On('location-updated')]
    public function updateLocation($latitude, $longitude): void
    {
        $this->data['latitude'] = $latitude;
        $this->data['longitude'] = $longitude;
    }
}
