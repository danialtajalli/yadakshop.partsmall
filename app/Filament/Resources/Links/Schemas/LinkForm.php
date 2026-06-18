<?php

namespace App\Filament\Resources\Links\Schemas;

use App\Enums\LinkType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LinkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('link_type')
                    ->options(LinkType::class)
                    ->required(),
                Select::make('company_id')
                    ->relationship('company', 'name'),
                Select::make('repair_shop_id')
                    ->relationship('repairShop', 'name'),
                Select::make('shop_id')
                    ->relationship('shop', 'name'),
            ]);
    }
}
