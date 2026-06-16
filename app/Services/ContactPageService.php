<?php

namespace App\Services;

class ContactPageService
{
    /**
     * @return array{
     *     contact: array{
     *         image_url: string,
     *         phone: string,
     *         mobile: string,
     *         email: string,
     *         address: string,
     *         hours: string,
     *     },
     * }
     */
    public function getContactMeta(): array
    {
        return [
            'contact' => config('partsmall.contact'),
        ];
    }
}
