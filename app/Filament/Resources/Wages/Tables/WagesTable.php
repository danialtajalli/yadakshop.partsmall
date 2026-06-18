<?php

namespace App\Filament\Resources\Wages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('نام اجرت')
                    ->searchable()->sortable(),
                TextColumn::make('variable')
                    ->numeric()->label('متغیر')
                    ->sortable()->searchable(),
                TextColumn::make('coefficient')
                    ->numeric()->label('ضریب')
                    ->sortable(),
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
