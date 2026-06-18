<?php

namespace App\Filament\Resources\Parts\RelationManagers;

use App\Filament\Resources\Shops\ShopResource;
use Filament\Actions\CreateAction;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class ShopsRelationManager extends RelationManager
{
    protected static ?string $title = 'فروشگاه ها';
    protected static string $relationship = 'shops';

    protected static ?string $relatedResource = ShopResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                // CreateAction::make()->label('ایجاد فروشگاه جدید'),
                AttachAction::make()->label('افزودن فروشگاه'),
            ])
            ->recordActions([
                DetachAction::make()->label('حذف فروشگاه'),
            ])
            ->toolbarActions(
                BulkActionGroup::make([
                    DetachBulkAction::make()->label('حذف فروشگاه های انتخاب شده'),
                ]),
            );
    }
}
