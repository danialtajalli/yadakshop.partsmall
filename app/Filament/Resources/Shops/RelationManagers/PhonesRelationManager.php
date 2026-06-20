<?php

namespace App\Filament\Resources\Shops\RelationManagers;

use App\Filament\Resources\Phones\PhoneResource;
use Filament\Actions\AssociateAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class PhonesRelationManager extends RelationManager
{
    protected static ?string $title = 'تلفن ها';
    protected static string $relationship = 'phones';

    protected static ?string $relatedResource = PhoneResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make()->label('افزودن تلفن'),
                AssociateAction::make()->label('اضافه کردن تلفن'),
            ])
            ->actions([
                DissociateAction::make()->label('حذف تلفن'),
            ]);
    }
}
