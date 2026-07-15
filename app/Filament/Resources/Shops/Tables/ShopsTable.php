<?php

namespace App\Filament\Resources\Shops\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShopsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->searchable()->label('نام فروشگاه')->toggleable(),
                TextColumn::make('secondary_name')
                    ->searchable()->label('نام ثانویه فروشگاه')->toggleable(),
                TextColumn::make('slug')
                    ->searchable()->label('نام لاتین فروشگاه')->toggleable(),
                IconColumn::make('confirmed')
                    ->boolean()->label('تایید شده')->toggleable(),
                IconColumn::make('show_under_product')
                    ->boolean()->label('نمایش تحت محصول')->toggleable(),
                TextColumn::make('person_responsible_name')
                    ->searchable()->label('نام مسئول فروشگاه')->toggleable(),
                TextColumn::make('person_responsible_email')
                    ->searchable()->label('ایمیل مسئول فروشگاه')->toggleable(),
                TextColumn::make('website_show')
                    ->searchable()->label('نمایش وبسایت')->toggleable(),
                TextColumn::make('order')
                    ->numeric()->label('ترتیب')
                    ->sortable()->toggleable(),
                TextColumn::make('latitude')
                    ->numeric()
                    ->sortable()->label('طول جغرافیایی')->toggleable(),
                TextColumn::make('longitude')
                    ->numeric()
                    ->sortable()->label('عرض جغرافیایی')->toggleable(),
                TextColumn::make('city.name')->label('شهر')
                    ->searchable()->toggleable(),
                TextColumn::make('city.state.name')->label('استان')
                    ->searchable()->toggleable(),
                TextColumn::make('address')->label('آدرس')
                    ->searchable()->toggleable(),
                TextColumn::make('open_time')->label('ساعت شروع کار')
                    ->time()
                    ->sortable()->toggleable(),
                TextColumn::make('close_time')->label('ساعت پایان کار')
                    ->time()
                    ->sortable()->toggleable(),
                TextColumn::make('open_time_friday')->label('ساعت شروع کار روز پنجشنبه')
                    ->time()
                    ->sortable()->toggleable(),
                TextColumn::make('close_time_friday')->label('ساعت پایان کار روز پنجشنبه')
                    ->time()
                    ->sortable()->toggleable(),
                TextColumn::make('open_time_thursday')->label('ساعت شروع کار روز جمعه')
                    ->time()
                    ->sortable()->toggleable(),
                TextColumn::make('close_time_thursday')->label('ساعت پایان کار روز جمعه')
                    ->time()
                    ->sortable()->toggleable(),
                IconColumn::make('off')->label('غیر فعال')
                    ->boolean()->toggleable(),
                TextColumn::make('created_at')
                    ->jalaliDateTime()->label('تاریخ ایجاد')
                    ->sortable()->toggleable(),
                TextColumn::make('updated_at')
                    ->jalaliDateTime()->label('تاریخ بروزرسانی')
                    ->sortable()->toggleable(isToggledHiddenByDefault: true),
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
