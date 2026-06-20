<?php

namespace App\Filament\Resources\Pages\Schemas;

use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')->label('عنوان'),
                TextInput::make('slug')->label('نام لاتین'),
                TinyEditor::make('content')->label('محتوا')
                    ->columnSpanFull(),
            ]);
    }
}
