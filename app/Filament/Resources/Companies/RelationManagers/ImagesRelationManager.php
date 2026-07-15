<?php

namespace App\Filament\Resources\Companies\RelationManagers;

use App\Filament\Resources\Images\Concerns\ConfiguresImagesRelationManager;
use App\Filament\Resources\Images\ImageResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
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
                $this->makeCreateImageAction('company_id', 'ساخت تصویر جدید'),
            ])
            ->actions([
                DissociateAction::make()->label('حذف تصویر'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make()->label('حذف تصاویر'),
                ]),
            ]);
    }
}
