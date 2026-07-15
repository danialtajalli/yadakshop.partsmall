<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                DateTimePicker::make('email_verified_at')->jalali(),
                TextInput::make('password')
                    ->password(),
                TextInput::make('username'),
                TextInput::make('topic'),
                Textarea::make('message')
                    ->columnSpanFull(),
            ]);
    }
}
