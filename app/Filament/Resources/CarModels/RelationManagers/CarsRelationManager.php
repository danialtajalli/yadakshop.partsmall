<?php

namespace App\Filament\Resources\CarModels\RelationManagers;

use App\Filament\Resources\Cars\CarResource;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class CarsRelationManager extends RelationManager
{
    protected static ?string $title = 'ماشین ها';
    protected static string $relationship = 'cars';

    protected static ?string $relatedResource = CarResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make()->label('افزودن ماشین'),
                AttachAction::make()->label('اضافه کردن ماشین'),
            ])
            ->actions([
                DetachAction::make()->label('حذف ماشین'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()->label('حذف ماشین'),
                ]),
            ])->inverseRelationship('models');
    }
}
