<?php

namespace App\Filament\Resources\Companies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('نام شرکت')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('slug')->label('نام لاتین شرکت')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('country')->label('کشور')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('wage_strike')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('links.name')->label('لینک‌ تلگرام')
                    ->numeric()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->jalaliDateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->jalaliDateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('wage_strike')
                    ->query(fn (Builder $query): Builder => $query->where('wage_strike', '>=', 1)),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
