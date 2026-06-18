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
                    ->searchable(),
                TextColumn::make('slug')->label('نام لاتین')
                    ->searchable(),
                TextColumn::make('category.name')->label('دسته بندی')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()->label('تاریخ ایجاد')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()->label('تاریخ بروزرسانی')
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
