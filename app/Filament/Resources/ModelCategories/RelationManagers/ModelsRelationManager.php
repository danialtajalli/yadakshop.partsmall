<?php

namespace App\Filament\Resources\ModelCategories\RelationManagers;

use App\Filament\Resources\CarModels\CarModelResource;
use Filament\Actions\AssociateAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class ModelsRelationManager extends RelationManager
{
    protected static ?string $title = 'مدل ها';
    protected static string $relationship = 'models';

    protected static ?string $relatedResource = CarModelResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                // CreateAction::make()->label('ساخت مدل جدید'),
                AssociateAction::make()->label('اضافه کردن مدل'),
            ])->inverseRelationship('category')
            ->recordActions([
                EditAction::make()->label('ویرایش مدل'),
                DissociateAction::make()->label('حذف مدل'),
            ]);
    }
}
