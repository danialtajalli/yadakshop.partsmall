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
                TextColumn::make('shop.name')
                    ->searchable()
                    ->label('فروشگاه')
                    ->toggleable(),
                TextColumn::make('repairShop.name')
                    ->searchable()
                    ->label('تعمیر گاه')
                    ->toggleable(),
                TextColumn::make('user.name')
                    ->searchable()
                    ->label('کاربر')
                    ->toggleable(),
                TextColumn::make('phone_number')
                    ->searchable()
                    ->label('شماره تلفن')
                    ->toggleable(),
                TextColumn::make('type')
                    ->badge()
                    ->label('نوع تلفن')
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
                EditAction::make()->label('ویرایش تلفن'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف تلفن ها'),
                ]),
            ]);
    }
}
