<?php

namespace App\Filament\Resources\RepairCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RepairCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('عنوان')
                    ->required(),
            ]);
    }
}
