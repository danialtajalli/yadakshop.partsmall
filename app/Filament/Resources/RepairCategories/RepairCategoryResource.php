<?php

namespace App\Filament\Resources\RepairCategories;

use App\Filament\Resources\RepairCategories\Pages\CreateRepairCategory;
use App\Filament\Resources\RepairCategories\Pages\EditRepairCategory;
use App\Filament\Resources\RepairCategories\Pages\ListRepairCategories;
use App\Filament\Resources\RepairCategories\Schemas\RepairCategoryForm;
use App\Filament\Resources\RepairCategories\Tables\RepairCategoriesTable;
use App\Models\RepairCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RepairCategoryResource extends Resource
{
    protected static ?string $navigationLabel = 'نوع مکانیک';
    protected static ?int $navigationSort = 9;
    protected static ?string $model = RepairCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return RepairCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RepairCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRepairCategories::route('/'),
            'create' => CreateRepairCategory::route('/create'),
            'edit' => EditRepairCategory::route('/{record}/edit'),
        ];
    }
}
