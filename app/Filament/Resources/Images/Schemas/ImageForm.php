<?php

namespace App\Filament\Resources\Images\Schemas;

use App\Enums\ImageType;
use App\Models\Image;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;

class ImageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->options(ImageType::class)
                    ->required()
                    ->live()
                    ->label('نوع'),
                Select::make('shop_id')
                    ->relationship('shop', 'name')
                    ->label('فروشگاه')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->default(fn (): ?int => request()->filled('shop_id') ? (int) request('shop_id') : null)
                    ->hidden(fn (Get $get): bool => ! self::shouldShowOwnerField('shop_id', $get) || self::isOwnerLocked('shop_id', $get))
                    ->disabled(fn (): bool => self::contextOwnerField() === 'shop_id'),
                Select::make('repair_shop_id')
                    ->relationship('repairShop', 'name')
                    ->label('تعمیرگاه')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->default(fn (): ?int => request()->filled('repair_shop_id') ? (int) request('repair_shop_id') : null)
                    ->hidden(fn (Get $get): bool => ! self::shouldShowOwnerField('repair_shop_id', $get) || self::isOwnerLocked('repair_shop_id', $get))
                    ->disabled(fn (): bool => self::contextOwnerField() === 'repair_shop_id'),
                Select::make('company_id')
                    ->relationship('company', 'name')
                    ->label('کمپانی')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->default(fn (): ?int => request()->filled('company_id') ? (int) request('company_id') : null)
                    ->hidden(fn (Get $get): bool => ! self::shouldShowOwnerField('company_id', $get) || self::isOwnerLocked('company_id', $get))
                    ->disabled(fn (): bool => self::contextOwnerField() === 'company_id'),
                FileUpload::make('path')
                    ->label('تصویر')
                    ->image()
                    ->openable()
                    ->required()
                    ->disk('public')
                    ->visibility('public')
                    ->directory(fn (Get $get): ?string => self::resolveUploadDirectory($get))
                    ->disabled(fn (Get $get): bool => blank(self::resolveUploadDirectory($get)))
                    ->helperText(fn (Get $get): string => blank($get('type'))
                        ? 'ابتدا نوع تصویر را انتخاب کنید.'
                        : (self::resolveOwner($get) === null
                            ? 'ابتدا یکی از فروشگاه / تعمیرگاه / کمپانی را انتخاب کنید.'
                            : 'تصویر در مسیر متناسب با نوع و مالک ذخیره می‌شود.'))
                    ->afterStateHydrated(function (FileUpload $component, Get $get): void {
                        $record = self::recordFromUploadComponent($component);
                        $state = $component->getState();

                        if (filled($state)) {
                            $expand = fn (mixed $file) => is_string($file)
                                ? (self::resolveStoragePath($get, $file, $record) ?? $file)
                                : $file;

                            $component->state(
                                $component->isMultiple()
                                    ? collect(Arr::wrap($state))->map($expand)->filter()->values()->all()
                                    : $expand($state),
                            );
                        }

                        $component->hydrateFiles();
                    })
                    ->getUploadedFileUsing(function (FileUpload $component, string $file, string | array | null $storedFileNames, Get $get): ?array {
                        $storagePath = self::resolveStoragePath($get, $file, self::recordFromUploadComponent($component));

                        if ($storagePath === null) {
                            return null;
                        }

                        return $component->getUploadedFile($storagePath, $storedFileNames);
                    })
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? basename($state) : null),
            ]);
    }

    protected static function recordFromUploadComponent(FileUpload $component): ?Image
    {
        $record = $component->getRecord();

        return $record instanceof Image ? $record : null;
    }

    /**
     * Matches config('partsmall.image_url'):
     * panel/assets/uploads/{model_type}/{image_type}/{model_id}/{image_name}
     *
     * Disk `public` is already rooted at panel/assets/uploads, so the upload
     * directory is {model_type}/{image_type}/{model_id}.
     */
    protected static function resolveUploadDirectory(Get $get, ?Image $record = null): ?string
    {
        $type = $get('type') ?? $record?->type;

        if (blank($type)) {
            return null;
        }

        $owner = self::resolveOwner($get, $record);

        if ($owner === null) {
            return null;
        }

        $imageType = $type instanceof ImageType ? $type->value : (string) $type;

        return $owner['model_type']!='company'?"{$owner['model_type']}/{$imageType}/{$owner['model_id']}":"{$owner['model_type']}/{$owner['model_id']}";
    }

    protected static function resolveStoragePath(Get $get, string $file, ?Image $record = null): ?string
    {
        if (str_contains($file, '/')) {
            return $file;
        }

        $directory = self::resolveUploadDirectory($get, $record);

        return $directory ? "{$directory}/{$file}" : null;
    }

    /**
     * @return array{model_type: string, model_id: int}|null
     */
    protected static function resolveOwner(Get $get, ?Image $record = null): ?array
    {
        if (filled($shopId = self::resolveOwnerId($get, 'shop_id', $record))) {
            return ['model_type' => 'shop', 'model_id' => $shopId];
        }

        if (filled($repairShopId = self::resolveOwnerId($get, 'repair_shop_id', $record))) {
            return ['model_type' => 'repair', 'model_id' => $repairShopId];
        }

        if (filled($companyId = self::resolveOwnerId($get, 'company_id', $record))) {
            return ['model_type' => 'company', 'model_id' => $companyId];
        }

        return null;
    }

    protected static function resolveOwnerId(Get $get, string $field, ?Image $record = null): ?int
    {
        $value = $get($field);

        if (blank($value) && $record !== null) {
            $value = $record->{$field};
        }

        if (blank($value)) {
            $value = request()->query($field);
        }

        if (blank($value)) {
            return null;
        }

        return (int) $value;
    }

    protected static function contextOwnerField(): ?string
    {
        foreach (['shop_id', 'repair_shop_id', 'company_id'] as $field) {
            if (request()->filled($field)) {
                return $field;
            }
        }

        return null;
    }

    protected static function shouldShowOwnerField(string $field, Get $get): bool
    {
        if ($context = self::contextOwnerField()) {
            return $context === $field;
        }

        $filledOwners = collect(['shop_id', 'repair_shop_id', 'company_id'])
            ->filter(fn (string $ownerField): bool => filled($get($ownerField)));

        if ($filledOwners->isEmpty()) {
            return true;
        }

        return $filledOwners->contains($field);
    }

    protected static function isOwnerLocked(string $field, Get $get): bool
    {
        if (! filled($get($field))) {
            return false;
        }

        return collect(['shop_id', 'repair_shop_id', 'company_id'])
            ->reject(fn (string $ownerField): bool => $ownerField === $field)
            ->every(fn (string $ownerField): bool => blank($get($ownerField)));
    }
}
