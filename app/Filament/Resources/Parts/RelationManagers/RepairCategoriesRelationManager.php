<?php

namespace App\Filament\Resources\Parts\RelationManagers;

use App\Filament\Resources\RepairCategories\RepairCategoryResource;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class RepairCategoriesRelationManager extends RelationManager
{
    protected static ?string $title = 'دسته بندی های نوع مکانیک';
    protected static string $relationship = 'repairCategories';

    protected static ?string $relatedResource = RepairCategoryResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                // CreateAction::make()->label('ایجاد دسته بندی تعمیرگاه جدید'),
                AttachAction::make()->label('افزودن دسته بندی نوع مکانیک'),
            ])
            ->recordActions([
                DetachAction::make()->label('حذف دسته بندی نوع مکانیک'),
            ])
            ->toolbarActions(
                BulkActionGroup::make([
                    DetachBulkAction::make()->label('حذف دسته بندی نوع مکانیک های انتخاب شده'),
                ]),
            );
    }
}
