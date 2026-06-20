<?php

namespace App\Filament\Resources\Companies\RelationManagers;

use App\Filament\Resources\Cars\CarResource;
use Filament\Actions\AssociateAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DissociateAction;
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
                AssociateAction::make()->label('اضافه کردن ماشین'),
            ])
            ->actions([
                DissociateAction::make()->label('حذف ماشین'),
            ]);
    }
}
