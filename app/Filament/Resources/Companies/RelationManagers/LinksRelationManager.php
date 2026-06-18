<?php

namespace App\Filament\Resources\Companies\RelationManagers;

use App\Filament\Resources\Links\LinkResource;
use Filament\Actions\AssociateAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class LinksRelationManager extends RelationManager
{
    protected static string $relationship = 'links';

    protected static ?string $relatedResource = LinkResource::class;

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
