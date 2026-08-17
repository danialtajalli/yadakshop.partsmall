<?php

namespace App\Filament\Resources\RequestLogs\Pages;

use App\Filament\Resources\RequestLogs\RequestLogResource;
use Filament\Resources\Pages\ListRecords;

class ListRequestLogs extends ListRecords
{
    protected static ?string $title = 'لاگ درخواست‌ها';

    protected static string $resource = RequestLogResource::class;
}
