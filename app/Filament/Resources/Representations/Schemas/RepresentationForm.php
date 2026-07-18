<?php

namespace App\Filament\Resources\Representations\Schemas;

use App\Enums\ImageType;
use App\Models\Representation;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class RepresentationForm
{
    public const LOGO_PENDING_DIRECTORY = 'representation/logo/pending';

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('نام')
                    ->required()
                    ->live(onBlur: true),
                TextInput::make('slug')
                    ->required()->label('نام لاتین')
                    ->live(onBlur: true),
                TextInput::make('responsible_person_name')->label('نام و نام خانوادگی نماینده'),
                TextInput::make('work_fields')->label('حوزه های کاری نماینده'),
                TextInput::make('mobile')->label('شماره تلفن همراه'),
                TextInput::make('telephone')
                    ->tel(),
                Select::make('company_id')
                    ->relationship('company', 'name')->label('نام شرکت')->searchable()->preload()
                    ->default(fn () => request('company_id')),
                TextInput::make('service_type')->label('نوع خدمات'),
                TextInput::make('website')
                    ->url(),
                TextInput::make('website_name')->label('نام وبسایت'),
                TextInput::make('whatsapp'),
                TextInput::make('whatsapp_phone')->label('شماره واتساپ')
                    ->tel(),
                TextInput::make('telegram')
                    ->tel(),
                TextInput::make('telegram_phone')->label('شماره تلگرام')
                    ->tel(),
                TextInput::make('instagram')->label('اینستاگرام'),
                Select::make('city_id')
                    ->relationship('city', 'name')->label('شهر')->searchable()->preload(),
                TextInput::make('address')->label('آدرس'),
                TextInput::make('latitude')->label('عرض جغرافیایی')
                    ->numeric(),
                TextInput::make('longitude')->label('طول جغرافیایی')
                    ->numeric(),
                Textarea::make('description')->label('توضیحات')
                    ->columnSpanFull(),
                FileUpload::make('logo')
                    ->label('لوگو')
                    ->image()
                    ->openable()
                    ->disk('public')
                    ->visibility('public')
                    ->directory(fn (FileUpload $component): string => self::resolveLogoDirectory($component))
                    ->helperText('تصویر با نام نمایندگی در مسیر لوگو ذخیره می‌شود.')
                    ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, Get $get, FileUpload $component): string {
                        $baseName = self::resolveLogoFileBaseName($get, self::recordFromUploadComponent($component));
                        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg');

                        return "{$baseName}.{$extension}";
                    })
                    ->afterStateHydrated(function (FileUpload $component): void {
                        $record = self::recordFromUploadComponent($component);
                        $state = $component->getState();

                        if (filled($state)) {
                            $expand = fn (mixed $file) => is_string($file)
                                ? (self::resolveLogoStoragePath($file, $record) ?? $file)
                                : $file;

                            $component->state(
                                $component->isMultiple()
                                    ? collect(Arr::wrap($state))->map($expand)->filter()->values()->all()
                                    : $expand($state),
                            );
                        }

                        $component->hydrateFiles();
                    })
                    ->getUploadedFileUsing(function (FileUpload $component, string $file, string | array | null $storedFileNames): ?array {
                        $storagePath = self::resolveLogoStoragePath($file, self::recordFromUploadComponent($component));

                        if ($storagePath === null) {
                            return null;
                        }

                        return $component->getUploadedFile($storagePath, $storedFileNames);
                    })
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? basename($state) : null),
                TextInput::make('nearby_railway')->label('راه آهن'),
                TextInput::make('nearby_bus')->label('اتوبوس'),
                TextInput::make('nearby_railway_name')->label('نام راه آهن'),
                TextInput::make('nearby_bus_name')->label('نام اتوبوس'),
                TextInput::make('nearby_railway_distance')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('nearby_bus_distance')->label('فاصله اتوبوس')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('show_under_product')->label('نمایش در محصول')
                    ->required(),
                View::make('components.view-product')->columnSpanFull(),
            ]);
    }

    public static function logoDirectoryForId(int | string $id): string
    {
        return 'representation/'.ImageType::Logo->value.'/'.$id;
    }

    protected static function resolveLogoDirectory(FileUpload $component): string
    {
        $record = self::recordFromUploadComponent($component);

        if ($record?->getKey()) {
            return self::logoDirectoryForId($record->getKey());
        }

        return self::LOGO_PENDING_DIRECTORY;
    }

    protected static function resolveLogoStoragePath(string $file, ?Representation $record = null): ?string
    {
        if (str_contains($file, '/')) {
            return $file;
        }

        if ($record?->getKey()) {
            return self::logoDirectoryForId($record->getKey()).'/'.$file;
        }

        return self::LOGO_PENDING_DIRECTORY.'/'.$file;
    }

    protected static function resolveLogoFileBaseName(Get $get, ?Representation $record = null): string
    {
        $base = self::sanitizeFileBaseName((string) ($get('name') ?: $record?->name ?: ''));

        if ($base === '') {
            $base = self::sanitizeFileBaseName((string) ($get('slug') ?: $record?->slug ?: ''));
        }

        return $base !== '' ? $base : 'logo';
    }

    protected static function recordFromUploadComponent(FileUpload $component): ?Representation
    {
        $record = $component->getRecord();

        return $record instanceof Representation ? $record : null;
    }

    protected static function sanitizeFileBaseName(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        $slug = Str::slug($value, '-');

        if ($slug !== '') {
            return Str::lower($slug);
        }

        $safe = preg_replace('/[^\p{L}\p{N}\-_]+/u', '-', $value) ?? '';
        $safe = trim($safe, '-_');

        return $safe === '' ? '' : Str::lower($safe);
    }
}
