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
                    ->searchable()->label('نام فروشگاه'),
                TextColumn::make('secondary_name')
                    ->searchable()->label('نام ثانویه فروشگاه'),
                TextColumn::make('slug')
                    ->searchable()->label('نام لاتین فروشگاه'),
                IconColumn::make('confirmed')
                    ->boolean()->label('تایید شده'),
                IconColumn::make('show_under_product')
                    ->boolean()->label('نمایش تحت محصول'),
                TextColumn::make('person_responsible_name')
                    ->searchable()->label('نام مسئول فروشگاه'),
                TextColumn::make('person_responsible_email')
                    ->searchable()->label('ایمیل مسئول فروشگاه'),
                TextColumn::make('website_show')
                    ->searchable()->label('نمایش وبسایت'),
                TextColumn::make('order')
                    ->numeric()->label('ترتیب')
                    ->sortable(),
                TextColumn::make('latitude')
                    ->numeric()
                    ->sortable()->label('طول جغرافیایی'),
                TextColumn::make('longitude')
                    ->numeric()
                    ->sortable()->label('عرض جغرافیایی'),
                TextColumn::make('city.name')->label('شهر')
                    ->searchable(),
                TextColumn::make('city.state.name')->label('استان')
                    ->searchable(),
                TextColumn::make('address')->label('آدرس')
                    ->searchable(),
                TextColumn::make('open_time')->label('ساعت شروع کار')
                    ->time()
                    ->sortable(),
                TextColumn::make('close_time')->label('ساعت پایان کار')
                    ->time()
                    ->sortable(),
                TextColumn::make('open_time_friday')->label('ساعت شروع کار روز پنجشنبه')
                    ->time()
                    ->sortable(),
                TextColumn::make('close_time_friday')->label('ساعت پایان کار روز پنجشنبه')
                    ->time()
                    ->sortable(),
                TextColumn::make('open_time_thursday')->label('ساعت شروع کار روز جمعه')
                    ->time()
                    ->sortable(),
                TextColumn::make('close_time_thursday')->label('ساعت پایان کار روز جمعه')
                    ->time()
                    ->sortable(),
                IconColumn::make('off')->label('غیر فعال')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()->label('تاریخ ایجاد')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->dateTime()->label('تاریخ بروزرسانی')
                    ->sortable()
                    ->toggleable(),
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
