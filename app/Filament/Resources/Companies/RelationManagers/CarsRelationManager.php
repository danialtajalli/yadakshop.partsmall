<?php

namespace App\Filament\Resources\Companies\RelationManagers;

use App\Filament\Resources\Cars\CarResource;
use Filament\Actions\AssociateAction;
use Filament\Actions\CreateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class CarsRelationManager extends RelationManager
{
    protected static string $relationship = 'cars';

    protected static ?string $relatedResource = CarResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
        ->recordActions([
                DissociateAction::make(),
            ])
        ->toolbarActions([
            BulkActionGroup::make([
                DissociateBulkAction::make(),
            ]),
        ]);
    }
}
