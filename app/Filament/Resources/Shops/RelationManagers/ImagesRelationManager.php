<?php

namespace App\Filament\Resources\Shops\RelationManagers;

use App\Filament\Resources\Images\Concerns\ConfiguresImagesRelationManager;
use App\Filament\Resources\Images\ImageResource;
use Filament\Actions\AssociateAction;
use Filament\Actions\DissociateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class ImagesRelationManager extends RelationManager
{
    use ConfiguresImagesRelationManager;

    protected static ?string $title = 'تصاویر';
    protected static string $relationship = 'images';

    protected static ?string $relatedResource = ImageResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                $this->makeCreateImageAction('shop_id'),
            ])
            ->actions([
                DissociateAction::make()->label('حذف از محصول'),
            ]);
    }
}
