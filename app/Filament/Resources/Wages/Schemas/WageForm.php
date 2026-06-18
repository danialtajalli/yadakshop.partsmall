<?php

namespace App\Filament\Resources\Wages\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()->label('نام اجرت'),
                TextInput::make('variable')
                    ->required()
                    ->numeric(),
                TextInput::make('coefficient')
                    ->required()
                    ->numeric()
                    ->default(1),
            ]);
    }
}
