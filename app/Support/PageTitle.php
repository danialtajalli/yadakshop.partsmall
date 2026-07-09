<?php

namespace App\Support;

class PageTitle
{
    public static function appendPageNumber(string $title, int $page): string
    {
        if ($page <= 1) {
            return $title;
        }

        return $title.' - صفحه '.$page;
    }
}
