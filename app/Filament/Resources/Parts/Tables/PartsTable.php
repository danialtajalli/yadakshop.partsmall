<?php

namespace App\Filament\Resources\Parts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PartsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('نام قطعه')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('slug')->label('نام لاتین قطعه')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('partsCategory.name')->label('دسته بندی قطعه')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->jalaliDateTime()
                    ->label('تاریخ ایجاد')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->jalaliDateTime()
                    ->label('تاریخ بروزرسانی')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
