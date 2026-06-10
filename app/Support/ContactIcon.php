<?php

namespace App\Support;

class ContactIcon
{
    public static function forKind(string $kind): string
    {
        return match ($kind) {
            'website' => 'fa-solid fa-globe',
            'instagram' => 'fa-brands fa-instagram',
            'telegram' => 'fa-brands fa-telegram',
            'whatsapp' => 'fa-brands fa-whatsapp',
            'mobile' => 'fa-solid fa-mobile-screen-button',
            'land' => 'fa-solid fa-phone',
            default => 'fa-solid fa-share-nodes',
        };
    }
}
