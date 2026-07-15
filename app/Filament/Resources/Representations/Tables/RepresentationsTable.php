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
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('slug')->label('نام لاتین')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('responsible_person_name')->label('نام و نام خانوادگی نماینده')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('work_fields')->label('حوزه های کاری نماینده')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('mobile')->label('شماره تلفن همراه')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('telephone')->label('تلفن')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('company.name')->label('نام شرکت')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('service_type')->label('نوع خدمات')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('website')->label('وبسایت')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('website_name')->label('نام وبسایت')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('whatsapp')->label('واتساپ')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('whatsapp_phone')->label('شماره واتساپ')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('telegram')->label('تلگرام')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('telegram_phone')->label('شماره تلگرام')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('instagram')->label('اینستاگرام')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('city.name')->label('شهر')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('city.state.name')->label('استان')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('address')->label('آدرس')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('latitude')->label('عرض جغرافیایی')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('longitude')->label('طول جغرافیایی')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('logo')->label('لوگو')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('nearby_railway')->label('راه آهن')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('nearby_bus')->label('اتوبوس')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('nearby_railway_name')->label('نام راه آهن')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('nearby_bus_name')->label('نام اتوبوس')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('nearby_railway_distance')->label('فاصله راه آهن')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('nearby_bus_distance')->label('فاصله اتوبوس')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('show_under_product')->label('نمایش در محصول')
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
