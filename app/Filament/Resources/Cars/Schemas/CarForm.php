<?php

namespace App\Filament\Resources\Cars\Schemas;

use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

class CarForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('نام ماشین')
                    ->required(),
                TinyEditor::make('description')
                    ->label('توضیحات')->rtl()
                    ->columnSpanFull(),
                TextInput::make('slug')
                    ->label('نام لاتین ماشین')
                    ->required(),
                Select::make('company_id')->searchable()
                    ->label('کمپانی')
                    ->relationship('company', 'name')
                    ->required()->searchable()->preload()->default(fn () => request('company_id')),
                Select::make('model_id')->searchable()
                    ->label('مدل')
                    ->relationship('models', 'name')
                    ->required()->searchable()->preload()->multiple(),
            ]);
    }
}
