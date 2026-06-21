<?php

namespace App\Filament\Resources\PartCategories;

use App\Filament\Resources\PartCategories\Pages\CreatePartCategory;
use App\Filament\Resources\PartCategories\Pages\EditPartCategory;
use App\Filament\Resources\PartCategories\Pages\ListPartCategories;
use App\Filament\Resources\PartCategories\Schemas\PartCategoryForm;
use App\Filament\Resources\PartCategories\Tables\PartCategoriesTable;
use App\Filament\Resources\PartCategories\RelationManagers\PartsRelationManager;
use App\Models\PartsCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PartCategoryResource extends Resource
{
    protected static ?string $navigationLabel = 'دسته بندی قطعات';
    protected static ?int $navigationSort = 13;
    protected static ?string $model = PartsCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PartCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PartCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PartsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPartCategories::route('/'),
            'create' => CreatePartCategory::route('/create'),
            'edit' => EditPartCategory::route('/{record}/edit'),
        ];
    }
}
