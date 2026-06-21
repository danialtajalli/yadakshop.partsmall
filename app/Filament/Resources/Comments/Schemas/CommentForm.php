<?php

namespace App\Filament\Resources\Comments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CommentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('fullname')->label('نام و نام خانوادگی'),
                TextInput::make('mobile')->label('شماره تلفن'),
                Textarea::make('body')
                    ->columnSpanFull(),
                TextInput::make('rating')->label('امتیاز')
                    ->numeric(),
                Toggle::make('confirmed')->label('تایید شده'),
                Select::make('shop_id')->label('فروشگاه')
                    ->relationship('shop', 'name')
                    ->required()->searchable()->preload(),
            ]);
    }
}
