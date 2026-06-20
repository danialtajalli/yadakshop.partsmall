<?php

namespace App\Filament\Resources\Shops\RelationManagers;

use App\Filament\Resources\Parts\PartResource;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\AttachAction;
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
                CreateAction::make()->label('افزودن قطعه'),
                AttachAction::make()->label('اضافه کردن قطعه'),
            ])
            ->actions([
                DetachAction::make()->label('حذف قطعه'),
            ]);
    }
}
