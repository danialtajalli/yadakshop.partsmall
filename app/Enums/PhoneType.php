<?php

namespace App\Enums;

enum PhoneType: string
{
    case Mobile = 'mobile';
    case Land = 'land';
    case Telegram = 'telegram';
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
            self::Mobile => 'تلفن همراه',
            self::Land => 'تلفن ثابت',
            self::Telegram => 'تلگرام',
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
            self::Mobile => 'fa-solid fa-mobile-screen-button',
            self::Land => 'fa-solid fa-phone',
            self::Telegram => 'fa-brands fa-telegram',
            self::Whatsapp => 'fa-brands fa-whatsapp',
            default => 'fa-solid fa-share-nodes',
        };
    }

    public function actionUrl(string $phoneNumber): string
    {
        $number = trim($phoneNumber);

        return match ($this) {
            self::Whatsapp => 'https://wa.me/'.preg_replace('/\D+/', '', $number),
            self::Telegram => str_starts_with($number, 'http')
                ? $number
                : 'https://t.me/'.ltrim($number, '@'),
            default => 'tel:'.$number,
        };
    }
}
