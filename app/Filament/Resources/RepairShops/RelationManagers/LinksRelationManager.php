<?php

namespace App\Filament\Resources\RepairShops\RelationManagers;

use App\Filament\Concerns\ConfiguresModalRelationCreate;
use App\Filament\Resources\Links\LinkResource;
use Filament\Actions\DissociateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class LinksRelationManager extends RelationManager
{
    use ConfiguresModalRelationCreate;

    protected static ?string $title = 'لینکها';
    protected static string $relationship = 'links';

    protected static ?string $relatedResource = LinkResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                $this->makeModalCreateAction('repair_shop_id', 'افزودن لینک'),
            ])
            ->actions([
                DissociateAction::make()->label('حذف لینک'),
            ])
            ->inverseRelationship('repairShop');
    }
}
