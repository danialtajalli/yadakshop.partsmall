<?php

namespace App\Filament\Resources\RepairShops\RelationManagers;

use App\Filament\Resources\RepairCategories\RepairCategoryResource;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class RepairCategoriesRelationManager extends RelationManager
{
    protected static ?string $title = 'دسته بندی های تعمیر';
    protected static string $relationship = 'repairCategories';

    protected static ?string $relatedResource = RepairCategoryResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make()->label('افزودن دسته بندی تعمیر'),
                AttachAction::make()->label('اضافه کردن دسته بندی تعمیر'),
            ])
            ->actions([
                DetachAction::make()->label('حذف دسته بندی تعمیر'),
            ]);
    }
}
