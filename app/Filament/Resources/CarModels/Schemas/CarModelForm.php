<?php

namespace App\Filament\Resources\CarModels\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CarModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()->label('نام مدل'),
                Textarea::make('description')
                    ->columnSpanFull()->label('توضیحات'),
                TextInput::make('slug')
                    ->required()->label('نام لاتین'),
                Select::make('category_id')
                    ->relationship('category', 'name'),
            ]);
    }
}
