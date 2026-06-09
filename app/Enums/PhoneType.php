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
}
