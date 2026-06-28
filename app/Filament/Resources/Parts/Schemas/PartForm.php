<?php

namespace App\Filament\Resources\Parts\Schemas;

use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PartForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()->label('نام قطعه'),
                TinyEditor::make('description')->rtl()
                    ->columnSpanFull()->label('توضیحات'),
                Textarea::make('category_description')
                    ->columnSpanFull()->label('توضیحات دسته بندی'),
                TextInput::make('slug')
                    ->required()->label('نام لاتین قطعه'),
                Select::make('parts_category_id')
                    ->label('دسته بندی قطعات')
                    ->relationship('partsCategory', 'name')->default(fn () => request('parts_category_id'))
                    ->required()->label('دسته بندی قطعه')->searchable()->preload(),
                Select::make('repair_categories_id')->relationship('repairCategories', 'name')->label('دسته بندی های نوع مکانیک')->searchable()->preload()->multiple(),
                Select::make('wages_id')->relationship('wages', 'name')->label('اجرت')->searchable()->preload()->multiple(),
                Select::make('shops_id')->relationship('shops', 'name')->label('فروشگاه ها')->searchable()->preload()->multiple(),
            ]);
    }
}
