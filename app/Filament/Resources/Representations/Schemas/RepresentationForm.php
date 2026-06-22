<?php

namespace App\Filament\Resources\Representations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class RepresentationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('نام')
                    ->required(),
                TextInput::make('slug')
                    ->required()->label('نام لاتین'),
                TextInput::make('responsible_person_name')->label('نام و نام خانوادگی نماینده'),
                TextInput::make('work_fields')->label('حوزه های کاری نماینده'),
                TextInput::make('mobile')->label('شماره تلفن همراه'),
                TextInput::make('telephone')
                    ->tel(),
                Select::make('company_id')
                    ->relationship('company', 'name')->label('نام شرکت')->searchable()->preload()
                    ->default(fn () => request('company_id')),
                TextInput::make('service_type')->label('نوع خدمات'),
                TextInput::make('website')
                    ->url(),
                TextInput::make('website_name')->label('نام وبسایت'),
                TextInput::make('whatsapp'),
                TextInput::make('whatsapp_phone')->label('شماره واتساپ')
                    ->tel(),
                TextInput::make('telegram')
                    ->tel(),
                TextInput::make('telegram_phone')->label('شماره تلگرام')
                    ->tel(),
                TextInput::make('instagram')->label('اینستاگرام'),
                Select::make('state_id')
                    ->relationship('state', 'name')->label('استان')->searchable()->preload(),
                Select::make('city_id')
                    ->relationship('city', 'name')->label('شهر')->searchable()->preload(),
                TextInput::make('address')->label('آدرس'),
                TextInput::make('latitude')->label('عرض جغرافیایی')
                    ->numeric(),
                TextInput::make('longitude')->label('طول جغرافیایی')
                    ->numeric(),
                Textarea::make('description')->label('توضیحات')
                    ->columnSpanFull(),
                TextInput::make('logo')->label('لوگو'),
                TextInput::make('nearby_railway')->label('راه آهن'),
                TextInput::make('nearby_bus')->label('اتوبوس'),
                TextInput::make('nearby_railway_name')->label('نام راه آهن'),
                TextInput::make('nearby_bus_name')->label('نام اتوبوس'),
                TextInput::make('nearby_railway_distance')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('nearby_bus_distance')->label('فاصله اتوبوس')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('show_under_product')->label('نمایش در محصول')
                    ->required(),
                View::make('components.view-product')->columnSpanFull(),
            ]);
    }
}
