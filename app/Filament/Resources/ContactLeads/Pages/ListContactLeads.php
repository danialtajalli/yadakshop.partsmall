<?php

namespace App\Filament\Resources\ContactLeads\Pages;

use App\Filament\Resources\ContactLeads\ContactLeadResource;
use Filament\Resources\Pages\ListRecords;

class ListContactLeads extends ListRecords
{
    protected static ?string $title = 'درخواست‌های تماس';

    protected static string $resource = ContactLeadResource::class;
}
