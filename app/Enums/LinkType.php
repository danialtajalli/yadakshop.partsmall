<?php

namespace App\Enums;

enum LinkType: string
{
    case Website = 'website';
    case Telegram = 'telegram';
    case Instagram = 'instagram';
    case Whatsapp = 'whatsapp';
    case Rubika = 'rubika';
    case Eita = 'eita';
    case Soroush = 'soroush';
    case Ble = 'ble';
    case Gap = 'gap';
    case Igap = 'igap';

    public function label(): string
    {
        return match ($this) {
            self::Website => 'وب‌سایت',
            self::Telegram => 'تلگرام',
            self::Instagram => 'اینستاگرام',
            self::Whatsapp => 'واتساپ',
            self::Rubika => 'روبیکا',
            self::Eita => 'ایتا',
            self::Soroush => 'سروش',
            self::Ble => 'بله',
            self::Gap => 'گپ',
            self::Igap => 'آی‌گپ',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Website => 'fa-solid fa-globe',
            self::Telegram => 'fa-brands fa-telegram',
            self::Instagram => 'fa-brands fa-instagram',
            self::Whatsapp => 'fa-brands fa-whatsapp',
            default => 'fa-solid fa-comment-dots',
        };
    }

    public function actionUrl(string $name): string
    {
        $value = trim($name);

        if ($value === '') {
            return '#';
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        if (str_starts_with($value, 't.me/') || str_starts_with($value, 'telegram.me/')) {
            return 'https://'.$value;
        }

        return match ($this) {
            self::Telegram => 'https://t.me/'.ltrim($value, '@'),
            self::Instagram => 'https://www.instagram.com/'.ltrim($value, '@/'),
            self::Whatsapp => str_contains($value, 'wa.me') || str_contains($value, 'whatsapp.com')
                ? 'https://'.ltrim($value, '/')
                : 'https://wa.me/'.preg_replace('/\D+/', '', $value),
            self::Website => 'https://'.ltrim($value, '/'),
            default => 'https://'.ltrim($value, '/'),
        };
    }

    public function displayName(string $name): string
    {
        $value = trim($name);

        if ($this !== self::Telegram || $value === '' || str_starts_with($value, '@')) {
            return $value;
        }

        if (str_starts_with(strtolower($value), 't.me')) {
            return '@'.$value;
        }

        return $value;
    }
}
