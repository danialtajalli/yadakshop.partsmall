<?php

namespace App\Filament\Resources\Comments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CommentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fullname')->label('نام و نام خانوادگی')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('shop.name')->label('فروشگاه')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('body')
                    ->label('نظر')
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('mobile')->label('شماره تلفن')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('rating')->label('امتیاز')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('confirmed')->label('تایید شده')
                    ->boolean()
                    ->toggleable(),
                TextColumn::make('created_at')->label('تاریخ ایجاد')
                    ->jalaliDateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')->label('تاریخ بروزرسانی')
                    ->jalaliDateTime()
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
