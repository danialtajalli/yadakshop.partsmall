<?php

namespace App\Filament\Resources\Companies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Filter;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('نام شرکت')
                    ->searchable(),
                TextColumn::make('slug')->label('نام لاتین شرکت')
                    ->searchable(),
                TextColumn::make('country')->label('کشور')
                    ->searchable(),
                TextColumn::make('wage_strike')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('links.name')->label('لینک‌ تلگرام')
                    ->numeric(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
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
