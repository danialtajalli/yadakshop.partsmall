<?php

namespace App\Filament\Resources\RepairCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class RepairCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('عنوان')
                    ->required(),
                Select::make('repair_shops_id')->relationship('repairShops', 'name')->label('تعمیرگاه ها')->searchable()->preload()->multiple()->columnSpanFull(),
                Select::make('parts_id')->relationship('parts', 'name')->label('قطعات')->searchable()->preload()->multiple()->columnSpanFull(),
            ]);
    }
}
