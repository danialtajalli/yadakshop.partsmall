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
                    ->label('فروشگاه'),
                TextColumn::make('repairShop.name')
                    ->searchable()
                    ->label('تعمیر گاه'),
                TextColumn::make('user.name')
                    ->searchable()
                    ->label('کاربر'),
                TextColumn::make('phone_number')
                    ->searchable()
                    ->label('شماره تلفن'),
                TextColumn::make('type')
                    ->badge()
                    ->label('نوع تلفن'),
                TextColumn::make('created_at')
                    ->jalaliDateTime()
                    ->sortable()
                    ->label('تاریخ ایجاد')
                    ->toggleable(isToggledHiddenByDefault: true),
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
