<?php

namespace App\Filament\Resources\CarModels\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CarModelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('نام مدل')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('slug')->label('نام لاتین')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('category.name')->label('دسته بندی')
                    ->searchable()
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
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
