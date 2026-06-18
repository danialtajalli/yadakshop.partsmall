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
                TextInput::make('fullname'),
                Select::make('shop_id')
                    ->relationship('shop', 'name')
                    ->required(),
                TextInput::make('mobile'),
                Textarea::make('body')
                    ->columnSpanFull(),
                TextInput::make('rating')
                    ->numeric(),
                Toggle::make('confirmed'),
            ]);
    }
}
