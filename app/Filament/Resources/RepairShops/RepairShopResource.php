<?php

namespace App\Filament\Resources\RepairShops;

use App\Filament\Resources\RepairShops\Pages\CreateRepairShop;
use App\Filament\Resources\RepairShops\Pages\EditRepairShop;
use App\Filament\Resources\RepairShops\Pages\ListRepairShops;
use App\Filament\Resources\RepairShops\Schemas\RepairShopForm;
use App\Filament\Resources\RepairShops\Tables\RepairShopsTable;
use App\Models\RepairShop;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Resources\RepairShops\RelationManagers\ImagesRelationManager;
use App\Filament\Resources\RepairShops\RelationManagers\PhonesRelationManager;
use App\Filament\Resources\RepairShops\RelationManagers\LinksRelationManager;
use App\Filament\Resources\RepairShops\RelationManagers\RepairCategoriesRelationManager;

class RepairShopResource extends Resource
{
    protected static ?string $navigationLabel = 'تعمیرگاه ها';
    protected static ?int $navigationSort = 8;
    protected static ?string $model = RepairShop::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return RepairShopForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RepairShopsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ImagesRelationManager::class,
            PhonesRelationManager::class,
            LinksRelationManager::class,
            RepairCategoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRepairShops::route('/'),
            'create' => CreateRepairShop::route('/create'),
            'edit' => EditRepairShop::route('/{record}/edit'),
        ];
    }
}
