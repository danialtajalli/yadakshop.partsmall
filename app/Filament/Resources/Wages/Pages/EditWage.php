<?php

namespace App\Filament\Resources\Wages\Pages;

use App\Filament\Resources\Wages\WageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWage extends EditRecord
{
    protected static ?string $title = 'ویرایش اجرت';
    protected static string $resource = WageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('حذف اجرت'),
        ];
    }
}
