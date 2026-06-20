<?php

namespace App\Filament\Resources\Phones\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PhonesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('shop.name')->label('فروشگاه')
                    ->searchable(),
                TextColumn::make('repairShop.name')
                    ->label('تعمیرگاه')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('کاربر')
                    ->searchable(),
                TextColumn::make('phone_number')
                    ->label('شماره تلفن')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('نوع')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('تاریخ بروزرسانی')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->label('ویرایش'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف'),
                ]),
            ]);
    }
}
