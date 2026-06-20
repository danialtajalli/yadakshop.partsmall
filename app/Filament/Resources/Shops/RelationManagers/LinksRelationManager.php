<?php

namespace App\Filament\Resources\Shops\RelationManagers;

use App\Filament\Resources\Links\LinkResource;
use Filament\Actions\AssociateAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class LinksRelationManager extends RelationManager
{
    protected static string $relationship = 'links';

    protected static ?string $relatedResource = LinkResource::class;

    protected static ?string $title = 'لینکها';

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make()->label('افزودن لینک'),
                AssociateAction::make()->label('اضافه کردن لینک'),
            ])
            ->actions([
                DissociateAction::make()->label('حذف لینک'),
            ]);
    }
}
