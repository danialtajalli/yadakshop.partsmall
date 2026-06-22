<?php

namespace App\Filament\Resources\RepairShops\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\View;

class RepairShopForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('عنوان')
                    ->required(),
                TextInput::make('slug')->label('نام لاتین')
                    ->required(),
                TextInput::make('responsible_person_name')->label('نام مسئول'),
                Textarea::make('work_description')
                    ->columnSpanFull()->label('توضیحات کاری'),
                Select::make('state_id')
                    ->relationship('state', 'name')->label('استان')->searchable()->preload(),
                TextInput::make('address')->label('آدرس'),
                TextInput::make('latitude')->label('عرض جغرافیایی')
                    ->numeric(),
                TextInput::make('longitude')->label('طول جغرافیایی')
                    ->numeric(),
                Textarea::make('description')
                    ->columnSpanFull()->label('توضیحات'),
                Select::make('repair_categories_id')->relationship('repairCategories', 'name')->label('دسته بندی های نوع مکانیک')->searchable()->preload()->multiple(),
                View::make('components.view-product')->columnSpanFull(),
            ]);
    }
}
