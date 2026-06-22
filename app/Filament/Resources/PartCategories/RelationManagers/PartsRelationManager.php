<?php

namespace App\Filament\Resources\PartCategories\RelationManagers;

use App\Filament\Resources\Parts\PartResource;
use Filament\Actions\AssociateAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DissociateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class PartsRelationManager extends RelationManager
{
    protected static ?string $title = 'قطعات';
    protected static string $relationship = 'parts';

    protected static ?string $relatedResource = PartResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                AssociateAction::make()->label('اضافه کردن قطعه'),
            ])
            ->actions([
                DissociateAction::make()->label('حذف قطعه'),
            ]);
    }
}
