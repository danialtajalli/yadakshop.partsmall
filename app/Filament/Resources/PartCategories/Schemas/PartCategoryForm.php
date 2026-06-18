<?php

namespace App\Filament\Resources\PartCategories\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;

class PartCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
