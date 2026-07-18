<?php

namespace App\Filament\Resources\Representations\Pages;

use App\Filament\Resources\Representations\RepresentationResource;
use App\Filament\Resources\Representations\Schemas\RepresentationForm;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateRepresentation extends CreateRecord
{
    protected static ?string $title = 'ایجاد نماینده جدید';

    protected static string $resource = RepresentationResource::class;

    protected function afterCreate(): void
    {
        $filename = $this->record->logo;

        if (blank($filename)) {
            return;
        }

        $filename = basename((string) $filename);
        $disk = Storage::disk('public');
        $from = RepresentationForm::LOGO_PENDING_DIRECTORY.'/'.$filename;
        $to = RepresentationForm::logoDirectoryForId($this->record->getKey()).'/'.$filename;

        if ($disk->exists($from)) {
            $disk->makeDirectory(dirname($to));
            $disk->move($from, $to);
        }

        if ($this->record->logo !== $filename) {
            $this->record->update(['logo' => $filename]);
        }
    }
}
