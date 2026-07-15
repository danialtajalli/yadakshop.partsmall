<?php

namespace App\Filament\Resources\Phones\Schemas;

use App\Enums\PhoneType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PhoneForm
{
    /** @var list<string> */
    protected const OWNER_FIELDS = ['shop_id', 'repair_shop_id', 'user_id'];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('phone_number')
                    ->tel()
                    ->required()
                    ->label('شماره تلفن'),
                Select::make('shop_id')
                    ->relationship('shop', 'name')
                    ->searchable()
                    ->preload()
                    ->default(fn (): ?int => request()->filled('shop_id') ? (int) request('shop_id') : null)
                    ->label('فروشگاه')
                    ->hidden(fn (Get $get): bool => ! self::shouldShowOwnerField('shop_id', $get)),
                Select::make('repair_shop_id')
                    ->relationship('repairShop', 'name')
                    ->searchable()
                    ->preload()
                    ->default(fn (): ?int => request()->filled('repair_shop_id') ? (int) request('repair_shop_id') : null)
                    ->label('تعمیر گاه')
                    ->hidden(fn (Get $get): bool => ! self::shouldShowOwnerField('repair_shop_id', $get)),
                Select::make('user_id')
                    ->relationship('user', 'username')
                    ->searchable()
                    ->preload()
                    ->default(fn (): ?int => request()->filled('user_id') ? (int) request('user_id') : null)
                    ->label('کاربر')
                    ->hidden(fn (Get $get): bool => ! self::shouldShowOwnerField('user_id', $get)),
                Select::make('type')
                    ->options(PhoneType::class)
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('نوع تلفن'),
            ]);
    }

    protected static function shouldShowOwnerField(string $field, Get $get): bool
    {
        if ($context = self::contextOwnerField()) {
            return $context === $field;
        }

        $filledOwners = collect(self::OWNER_FIELDS)
            ->filter(fn (string $ownerField): bool => filled($get($ownerField)));

        if ($filledOwners->isEmpty()) {
            return true;
        }

        if ($filledOwners->contains($field)) {
            return ! self::isOwnerLocked($field, $get);
        }

        return false;
    }

    protected static function contextOwnerField(): ?string
    {
        foreach (self::OWNER_FIELDS as $field) {
            if (request()->filled($field)) {
                return $field;
            }
        }

        return null;
    }

    protected static function isOwnerLocked(string $field, Get $get): bool
    {
        if (! filled($get($field))) {
            return false;
        }

        return collect(self::OWNER_FIELDS)
            ->reject(fn (string $ownerField): bool => $ownerField === $field)
            ->every(fn (string $ownerField): bool => blank($get($ownerField)));
    }
}
