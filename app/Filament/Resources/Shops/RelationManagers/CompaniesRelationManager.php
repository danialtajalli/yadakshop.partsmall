<?php

namespace App\Filament\Resources\Shops\RelationManagers;

use App\Filament\Resources\Companies\CompanyResource;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\AttachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class CompaniesRelationManager extends RelationManager
{
    protected static ?string $title = 'شرکت ها';
    protected static string $relationship = 'companies';

    protected static ?string $relatedResource = CompanyResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make()->label('افزودن شرکت'),
                AttachAction::make()->label('اضافه کردن شرکت'),
            ])
            ->actions([
                DetachAction::make()->label('حذف شرکت'),
            ]);
    }
}
