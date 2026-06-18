<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->placeholder('-'),
                TextEntry::make('email')
                    ->label('Email address')
                    ->placeholder('-'),
                TextEntry::make('email_verified_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->placeholder('-'),
                TextEntry::make('username')
                    ->placeholder('-'),
                TextEntry::make('topic')
                    ->placeholder('-'),
                TextEntry::make('message')
                    ->placeholder('-')
                    ->columnSpanFull(),
            ]);
    }
}
