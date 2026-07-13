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
                TextColumn::make('name')->label('نام')
                    ->searchable(),
                TextColumn::make('slug')->label('نام لاتین')
                    ->searchable(),
                TextColumn::make('responsible_person_name')->label('نام و نام خانوادگی نماینده')
                    ->searchable(),
                TextColumn::make('work_fields')->label('حوزه های کاری نماینده')
                    ->searchable(),
                TextColumn::make('mobile')->label('شماره تلفن همراه')
                    ->searchable(),
                TextColumn::make('telephone')->label('تلفن')
                    ->searchable(),
                TextColumn::make('company.name')->label('نام شرکت')
                    ->searchable(),
                TextColumn::make('service_type')->label('نوع خدمات')
                    ->searchable(),
                TextColumn::make('website')->label('وبسایت')
                    ->searchable(),
                TextColumn::make('website_name')->label('نام وبسایت')
                    ->searchable(),
                TextColumn::make('whatsapp')->label('واتساپ')
                    ->searchable(),
                TextColumn::make('whatsapp_phone')->label('شماره واتساپ')
                    ->searchable(),
                TextColumn::make('telegram')->label('تلگرام')
                    ->searchable(),
                TextColumn::make('telegram_phone')->label('شماره تلگرام')
                    ->searchable(),
                TextColumn::make('instagram')->label('اینستاگرام')
                    ->searchable(),
                TextColumn::make('city.name')->label('شهر')
                    ->searchable(),
                TextColumn::make('city.state.name')->label('استان')
                    ->searchable(),
                TextColumn::make('address')->label('آدرس')
                    ->searchable(),
                TextColumn::make('latitude')->label('عرض جغرافیایی')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('longitude')->label('طول جغرافیایی')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('logo')->label('لوگو')
                    ->searchable(),
                TextColumn::make('nearby_railway')->label('راه آهن')
                    ->searchable(),
                TextColumn::make('nearby_bus')->label('اتوبوس')
                    ->searchable(),
                TextColumn::make('nearby_railway_name')->label('نام راه آهن')
                    ->searchable(),
                TextColumn::make('nearby_bus_name')->label('نام اتوبوس')
                    ->searchable(),
                TextColumn::make('nearby_railway_distance')->label('فاصله راه آهن')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nearby_bus_distance')->label('فاصله اتوبوس')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('show_under_product')->label('نمایش در محصول')
                    ->boolean(),
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
