<?php

namespace App\Filament\Resources\Wages;

use App\Filament\Resources\Wages\Pages\CreateWage;
use App\Filament\Resources\Wages\Pages\EditWage;
use App\Filament\Resources\Wages\Pages\ListWages;
use App\Filament\Resources\Wages\Schemas\WageForm;
use App\Filament\Resources\Wages\Tables\WagesTable;
use App\Filament\Resources\Wages\RelationManagers\PartsRelationManager;
use App\Models\Wage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WageResource extends Resource
{
    protected static ?string $navigationLabel = 'اجرت';
    protected static ?int $navigationSort = 10;
    protected static ?string $model = Wage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return WageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WagesTable::configure($table);
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
            'index' => ListWages::route('/'),
            'create' => CreateWage::route('/create'),
            'edit' => EditWage::route('/{record}/edit'),
        ];
    }
}
