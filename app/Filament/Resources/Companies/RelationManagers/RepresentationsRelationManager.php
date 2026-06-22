<?php

namespace App\Filament\Resources\Companies\RelationManagers;

use App\Filament\Resources\Representations\RepresentationResource;
use Filament\Actions\AssociateAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class RepresentationsRelationManager extends RelationManager
{
    protected static ?string $title = 'نمایندگی ها';
    protected static string $relationship = 'representations';

    protected static ?string $relatedResource = RepresentationResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                AssociateAction::make()->label('اضافه کردن نمایندگی'),
            ])
            ->actions([
                DissociateAction::make()->label('حذف نمایندگی'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make()->label('حذف نمایندگی'),
                ]),
            ]);
    }
}
