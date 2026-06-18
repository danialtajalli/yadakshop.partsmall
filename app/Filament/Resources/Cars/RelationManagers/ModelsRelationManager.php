<?php

namespace App\Filament\Resources\Cars\RelationManagers;

use App\Filament\Resources\Cars\CarResource;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\AttachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class ModelsRelationManager extends RelationManager
{
    protected static ?string $title = 'مدل ها';

    protected static string $relationship = 'models';

    protected static ?string $relatedResource = CarResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                // CreateAction::make()->label('ایجاد مدل جدید'),
                AttachAction::make(),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions(
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            );
    }
}
