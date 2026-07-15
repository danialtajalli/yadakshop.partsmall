<?php

namespace App\Filament\Resources\Shops\RelationManagers;

use App\Filament\Concerns\ConfiguresModalRelationCreate;
use App\Filament\Resources\Links\LinkResource;
use Filament\Actions\DissociateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class LinksRelationManager extends RelationManager
{
    use ConfiguresModalRelationCreate;

    protected static string $relationship = 'links';

    protected static ?string $relatedResource = LinkResource::class;

    protected static ?string $title = 'لینکها';

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                $this->makeModalCreateAction('shop_id', 'افزودن لینک'),
            ])
            ->actions([
                DissociateAction::make()->label('حذف لینک'),
            ]);
    }
}
