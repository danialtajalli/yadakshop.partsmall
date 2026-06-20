<?php

namespace App\Filament\Resources\Shops\RelationManagers;

use App\Filament\Resources\PartCategories\PartCategoryResource;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class PartCategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'partsCategories';

    protected static ?string $relatedResource = PartCategoryResource::class;

    protected static ?string $title = 'دسته بندی های قطعات';

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make()->label('افزودن دسته بندی قطعات'),
                AttachAction::make()->label('اضافه کردن دسته بندی قطعات'),
            ])
            ->actions([
                DetachAction::make()->label('حذف دسته بندی قطعات'),
            ]);
    }
}
