<?php

namespace App\Filament\Resources\RepairShops\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class RepairShopForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('responsible_person_name'),
                Textarea::make('work_description')
                    ->columnSpanFull(),
                Select::make('state_id')
                    ->relationship('state', 'name'),
                TextInput::make('address'),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
                Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }
}
