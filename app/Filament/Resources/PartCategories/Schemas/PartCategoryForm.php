<?php

namespace App\Filament\Resources\PartCategories\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

class PartCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()->label('نام')
                    ->maxLength(255),
                Select::make('shops')
                    ->relationship('shops', 'name')->label('فروشگاه ها')->searchable()->preload()->multiple(),
            ]);
    }
}
