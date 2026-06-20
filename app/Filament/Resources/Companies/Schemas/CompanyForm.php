<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->label('نام'),
                Textarea::make('description')
                    ->columnSpanFull()
                    ->label('توضیحات'),
                TextInput::make('slug')
                    ->label('نام لاتین')
                    ->required(),
                TextInput::make('country')
                    ->label('کشور'),
                TextInput::make('wage_strike')->label('ضریب اجرت')
                    ->required()
                    ->numeric()
                    ->default(1),
            ]);
    }
}
