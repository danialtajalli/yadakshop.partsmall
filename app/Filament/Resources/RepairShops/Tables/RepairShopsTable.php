<?php

namespace App\Filament\Resources\RepairShops\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RepairShopsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('عنوان')
                    ->searchable(),
                TextColumn::make('slug')->label('نام لاتین')
                    ->searchable(),
                TextColumn::make('responsible_person_name')->label('نام مسئول')
                    ->searchable(),
                TextColumn::make('state.name')->label('استان')
                    ->searchable(),
                TextColumn::make('address')->label('آدرس')
                    ->searchable(),
                TextColumn::make('latitude')->label('عرض جغرافیایی')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('longitude')->label('طول جغرافیایی')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')->label('تاریخ ایجاد')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('تاریخ بروزرسانی')
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
