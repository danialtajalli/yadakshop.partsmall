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
                    ->badge()->label('نوع'),
                TextColumn::make('path')
                    ->searchable()->label('آدرس')->sortable(),
                TextColumn::make('company.name')
                    ->searchable()->label('نام شرکت')->sortable(),
                TextColumn::make('repairShop.name')
                    ->searchable()->label('نام تعمیرگاه')->sortable(),
                TextColumn::make('shop.name')
                    ->searchable()->label('نام فروشگاه')->sortable(),
                TextColumn::make('created_at')
                    ->jalaliDateTime()
                    ->sortable()->label('تاریخ ایجاد')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->jalaliDateTime()
                    ->sortable()->label('تاریخ بروزرسانی')
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
