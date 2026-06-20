<?php

namespace App\Filament\Resources\RepairCategories\RelationManagers;

use App\Filament\Resources\RepairShops\RepairShopResource;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class RepairShopsRelationManager extends RelationManager
{
    protected static ?string $title = 'تعمیرگاه ها';
    protected static string $relationship = 'repairShops';

    protected static ?string $relatedResource = RepairShopResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make()->label('افزودن تعمیرگاه'),
                AttachAction::make()->label('اضافه کردن تعمیرگاه'),
            ])
            ->actions([
                DetachAction::make()->label('حذف تعمیرگاه'),
            ]);
    }
}
