<?php

namespace App\Filament\Resources\Images\Schemas;

use App\Enums\ImageType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ImageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->options(ImageType::class)
                    ->required()->label('نوع'),
                TextInput::make('path')
                    ->required()->label('آدرس'),
                Select::make('repair_shop_id')
                    ->relationship('repairShop', 'name')->label('شناسه تعمیرگاه')->searchable()->preload(),
                Select::make('shop_id')
                    ->relationship('shop', 'name')->label('شناسه فروشگاه')->searchable()->preload(),
                Select::make('company_id')
                    ->relationship('company', 'name')->label('شناسه شرکت')->searchable()->preload(),
            ]);
    }
}
