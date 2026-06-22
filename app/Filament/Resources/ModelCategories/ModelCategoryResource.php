<?php

namespace App\Filament\Resources\ModelCategories;

use App\Filament\Resources\ModelCategories\Pages\CreateModelCategory;
use App\Filament\Resources\ModelCategories\Pages\EditModelCategory;
use App\Filament\Resources\ModelCategories\Pages\ListModelCategories;
use App\Filament\Resources\ModelCategories\RelationManagers\ModelsRelationManager;
use App\Filament\Resources\ModelCategories\Schemas\ModelCategoryForm;
use App\Filament\Resources\ModelCategories\Tables\ModelCategoriesTable;
use App\Models\ModelCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ModelCategoryResource extends Resource
{
    protected static ?string $navigationLabel = 'دسته بندی مدل ها';
    protected static UnitEnum|string|null $navigationGroup = 'قسمت های جدید';
    protected static ?int $navigationSort = 1;
    protected static ?string $model = ModelCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ModelCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ModelCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ModelsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListModelCategories::route('/'),
            'create' => CreateModelCategory::route('/create'),
            'edit' => EditModelCategory::route('/{record}/edit'),
        ];
    }
}
