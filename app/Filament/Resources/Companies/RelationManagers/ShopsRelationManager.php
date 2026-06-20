<?php

namespace App\Filament\Resources\Companies\RelationManagers;

use App\Filament\Resources\Shops\ShopResource;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\AttachAction;
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
                CreateAction::make()->label('افزودن فروشگاه'),
                AttachAction::make()->label('اضافه کردن فروشگاه'),
            ])
            ->actions([
                DetachAction::make()->label('حذف فروشگاه'),
            ]);
    }
}
