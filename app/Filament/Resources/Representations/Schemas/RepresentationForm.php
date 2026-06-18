<?php

namespace App\Filament\Resources\Representations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RepresentationForm
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
                TextInput::make('work_fields'),
                TextInput::make('mobile'),
                TextInput::make('telephone')
                    ->tel(),
                Select::make('company_id')
                    ->relationship('company', 'name'),
                TextInput::make('service_type'),
                TextInput::make('website')
                    ->url(),
                TextInput::make('website_name'),
                TextInput::make('whatsapp'),
                TextInput::make('whatsapp_phone')
                    ->tel(),
                TextInput::make('telegram')
                    ->tel(),
                TextInput::make('telegram_phone')
                    ->tel(),
                TextInput::make('instagram'),
                Select::make('state_id')
                    ->relationship('state', 'name'),
                Select::make('city_id')
                    ->relationship('city', 'name'),
                TextInput::make('address'),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('logo'),
                TextInput::make('nearby_railway'),
                TextInput::make('nearby_bus'),
                TextInput::make('nearby_railway_name'),
                TextInput::make('nearby_bus_name'),
                TextInput::make('nearby_railway_distance')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('nearby_bus_distance')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('show_under_product')
                    ->required(),
            ]);
    }
}
