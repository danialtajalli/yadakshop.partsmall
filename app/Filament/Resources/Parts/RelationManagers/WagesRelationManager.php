<?php

namespace App\Filament\Resources\Parts\RelationManagers;

use App\Filament\Resources\Wages\WageResource;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class WagesRelationManager extends RelationManager
{
    protected static ?string $title = 'اجرت';
    protected static string $relationship = 'wages';

    protected static ?string $relatedResource = WageResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                // CreateAction::make()->label('ایجاد اجرت جدید'),
                AttachAction::make()->label('افزودن اجرت'),
            ])
            ->recordActions([
                DetachAction::make()->label('حذف اجرت'),
            ])
            ->toolbarActions(
                BulkActionGroup::make([
                    DetachBulkAction::make()->label('حذف اجرت های انتخاب شده'),
                ]),
            );
    }
}
