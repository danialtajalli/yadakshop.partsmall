<?php

namespace App\Filament\Resources\Representations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RepresentationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('responsible_person_name')
                    ->searchable(),
                TextColumn::make('work_fields')
                    ->searchable(),
                TextColumn::make('mobile')
                    ->searchable(),
                TextColumn::make('telephone')
                    ->searchable(),
                TextColumn::make('company.name')
                    ->searchable(),
                TextColumn::make('service_type')
                    ->searchable(),
                TextColumn::make('website')
                    ->searchable(),
                TextColumn::make('website_name')
                    ->searchable(),
                TextColumn::make('whatsapp')
                    ->searchable(),
                TextColumn::make('whatsapp_phone')
                    ->searchable(),
                TextColumn::make('telegram')
                    ->searchable(),
                TextColumn::make('telegram_phone')
                    ->searchable(),
                TextColumn::make('instagram')
                    ->searchable(),
                TextColumn::make('state.name')
                    ->searchable(),
                TextColumn::make('city.name')
                    ->searchable(),
                TextColumn::make('address')
                    ->searchable(),
                TextColumn::make('latitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('longitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('logo')
                    ->searchable(),
                TextColumn::make('nearby_railway')
                    ->searchable(),
                TextColumn::make('nearby_bus')
                    ->searchable(),
                TextColumn::make('nearby_railway_name')
                    ->searchable(),
                TextColumn::make('nearby_bus_name')
                    ->searchable(),
                TextColumn::make('nearby_railway_distance')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nearby_bus_distance')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('show_under_product')
                    ->boolean(),
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
