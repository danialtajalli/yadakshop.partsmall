<?php

namespace App\Filament\Resources\Phones\Schemas;

use App\Enums\PhoneType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PhoneForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('phone_number')
                    ->tel()
                    ->required()
                    ->label('شماره تلفن'),
                Select::make('shop_id')
                    ->relationship('shop', 'name')->searchable()->preload()->default(fn () => request('shop_id'))
                    ->label('فروشگاه'),
                Select::make('repair_shop_id')
                    ->relationship('repairShop', 'name')->searchable()->preload()
                    ->label('تعمیر گاه')->default(fn () => request('repair_shop_id')),
                Select::make('user_id')
                    ->relationship('user', 'name')->searchable()->preload()->default(fn () => request('user_id'))
                    ->label('کاربر'),
                Select::make('type')
                    ->options(PhoneType::class)->searchable()->preload()
                    ->required()
                    ->label('نوع تلفن'),
            ]);
    }
}
