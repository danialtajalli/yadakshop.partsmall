<?php

namespace App\Filament\Resources\Comments\Pages;

use App\Filament\Resources\Comments\CommentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListComments extends ListRecords
{
    protected static ?string $title = 'لیست نظرات';
    protected static string $resource = CommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('ایجاد نظر جدید'),
        ];
    }
}
