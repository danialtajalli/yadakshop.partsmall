<?php

namespace App\Filament\Resources\ModelCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ModelCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()->label('عنوان'),
                TextInput::make('slug')
                    ->required()->label('نام لاتین'),
            ]);
    }
}
