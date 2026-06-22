<?php

namespace App\Filament\Resources\Images\Schemas;

use App\Enums\ImageType;
use Filament\Forms\Components\FileUpload;
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
                    FileUpload::make('path')->openable()
                    ->required()->label('تصویر')->image(),
                Select::make('repair_shop_id')
                    ->relationship('repairShop', 'name')->label('تعمیرگاه')->searchable()->preload()->default(fn () => request('repair_shop_id')),
                Select::make('shop_id')
                    ->relationship('shop', 'name')->label('فروشگاه')->searchable()->preload()->default(fn () => request('shop_id')),
                Select::make('company_id')
                    ->relationship('company', 'name')->label('کمپانی')->searchable()->preload()->default(fn () => request('company_id')),
            ]);
    }
}
