<?php

namespace App\Filament\Resources\Shops\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class ShopForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()->label('نام فروشگاه'),
                TextInput::make('secondary_name')->label('نام ثانویه فروشگاه'),
                TextInput::make('slug')
                    ->required()->label('نام لاتین فروشگاه'),
                Toggle::make('confirmed')
                    ->required()->label('تایید شده'),
                Toggle::make('show_under_product')
                    ->required()->label('نمایش تحت محصول'),
                Textarea::make('description')
                    ->columnSpanFull()->label('توضیحات'),
                TextInput::make('person_responsible_name')->label('نام مسئول فروشگاه'),
                TextInput::make('person_responsible_email')
                    ->email()->label('ایمیل مسئول فروشگاه'),
                TextInput::make('website_show')->label('نمایش وبسایت'),
                TextInput::make('order')->label('ترتیب')
                    ->numeric(),
                TextInput::make('latitude')
                    ->numeric()->label('طول جغرافیایی'),
                TextInput::make('longitude')
                    ->numeric()->label('عرض جغرافیایی'),
                TextInput::make('address')->label('آدرس'),
                TimePicker::make('open_time')
                    ->required()->label('ساعت شروع کار'),
                TimePicker::make('close_time')
                    ->required()->label('ساعت پایان کار'),
                TimePicker::make('open_time_friday')->label('ساعت شروع کار روز جمعه'),
                TimePicker::make('close_time_friday')->label('جمعهساعت پایان کار روز جمعه'),
                TimePicker::make('open_time_thursday')->label('ساعت شروع کار روز پنجشنبه'),
                TimePicker::make('close_time_thursday')->label('ساعت پایان کار روز پنجشنبه'),
                Toggle::make('off')
                    ->required()->label('ایا فروشگاه تخفیف دارد؟'),
                Select::make('state')
                    ->relationship('state', 'name')
                    ->searchable()->preload()->label('استان'),
                Select::make('parts_categories_id')->relationship('partsCategories', 'name')->label('دسته بندی های قطعات')->searchable()->preload()->multiple(),
                Select::make('parts_id')->relationship('parts', 'name')->label('قطعات')->searchable()->preload()->multiple(),
                Select::make('companies_id')->relationship('companies', 'name')->label('شرکت ها')->searchable()->preload()->multiple(),
                View::make('components.view-product')->columnSpanFull(),
            ]);
    }
}
