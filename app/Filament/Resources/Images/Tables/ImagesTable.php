<?php

namespace App\Filament\Resources\Images\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ImagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->badge()
                    ->label('نوع')
                    ->toggleable(),
                TextColumn::make('path')
                    ->searchable()
                    ->label('آدرس')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('company.name')
                    ->searchable()
                    ->label('نام شرکت')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('repairShop.name')
                    ->searchable()
                    ->label('نام تعمیرگاه')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('shop.name')
                    ->searchable()
                    ->label('نام فروشگاه')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->jalaliDateTime()
                    ->sortable()
                    ->label('تاریخ ایجاد')
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->jalaliDateTime()
                    ->sortable()
                    ->label('تاریخ بروزرسانی')
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
