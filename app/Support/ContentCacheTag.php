<?php

namespace App\Support;

final class ContentCacheTag
{
    public const HOME = 'content-home';

    public const CATALOG = 'content-catalog';

    public const DIRECTORY = 'content-directory';

    public const PRODUCT = 'content-product';

    public const PAGES = 'content-pages';

    public const PART_PAGE = 'content-part-page';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::HOME,
            self::CATALOG,
            self::DIRECTORY,
            self::PRODUCT,
            self::PAGES,
            self::PART_PAGE,
        ];
    }
}
