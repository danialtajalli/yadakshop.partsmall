<?php

namespace App\Filament\Resources\RepairShops\RelationManagers;

use App\Filament\Resources\Images\ImageResource;
use Filament\Actions\AssociateAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DissociateAction;
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
                CreateAction::make()->label('افزودن تصویر'),
                AssociateAction::make()->label('اضافه کردن تصویر'),
            ])
            ->actions([
                DissociateAction::make()->label('حذف تصویر'),
            ])
            ->inverseRelationship('repairShop');
    }
}
