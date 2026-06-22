<?php

namespace App\Filament\Resources\Shops\RelationManagers;

use App\Filament\Resources\Images\ImageResource;
use Filament\Actions\AssociateAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class ImagesRelationManager extends RelationManager
{
    protected static ?string $title = 'تصاویر';
    protected static string $relationship = 'images';

    protected static ?string $relatedResource = ImageResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make()->label('افزودن تصویر')
                ->url(fn () => ImageResource::getUrl('create', [
                    'shop_id' => $this->getOwnerRecord()->id,
                ])),
                AssociateAction::make()->label('اضافه کردن تصویر'),
            ])
            ->actions([
                DissociateAction::make()->label('حذف از محصول'),
            ]);
    }
}
