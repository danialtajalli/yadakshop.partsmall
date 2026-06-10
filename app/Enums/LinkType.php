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
            default => 'fa-solid fa-share-nodes',
        };
    }
}
