<?php

namespace App\Support;

class MetaDescription
{
    public const SITE_NAME = 'پارتس‌مال';

    public static function home(): string
    {
        return 'لوازم یدکی خودرو، فروشگاه‌ها، تعمیرگاه‌ها، نمایندگی‌ها و قطعات خودرو را در پارتس‌مال یکجا پیدا کنید.';
    }

    public static function shopProfile(string $shopName): string
    {
        return 'مشاهده اطلاعات تماس، امتیاز، نظرات، موقعیت و راه‌های ارتباطی فروشگاه '.$shopName.' در پارتس‌مال.';
    }

    public static function repairShopProfile(string $name): string
    {
        return 'مشاهده اطلاعات تماس، خدمات، موقعیت و راه‌های ارتباطی تعمیرگاه '.$name.' در پارتس‌مال.';
    }

    public static function representationProfile(string $name): string
    {
        return 'مشاهده اطلاعات تماس، خدمات و راه‌های ارتباطی نمایندگی '.$name.' در پارتس‌مال.';
    }

    public static function product(string $label): string
    {
        return self::limit('قیمت و فروشندگان '.$label.' را بررسی کنید؛ همراه با تعمیرگاه‌ها، اجرت‌های مرتبط و راه‌های تماس فروشگاه‌ها در پارتس‌مال.');
    }

    public static function part(string $partName): string
    {
        return 'مشاهده خودروهای مرتبط، جزئیات، فروشندگان و قیمت '.$partName.'همه خودرو ها در پارتس‌مال.';
    }

    public static function listing(string $title): string
    {
        $title = self::plainText($title);

        return match (true) {
            self::containsAny($title, ['فروشگاه']) => self::listingShops($title),
            self::containsAny($title, ['تعمیرگاه']) => self::listingRepairShops($title),
            self::containsAny($title, ['نمایندگی']) => self::listingRepresentations($title),
            default => self::limit('جستجو، فیلتر و مشاهده '.$title.' همراه با جزئیات، موقعیت و راه‌های ارتباطی در پارتس‌مال.'),
        };
    }

    public static function vehicleParts(string $label): string
    {
        return self::limit('فهرست لوازم یدکی '.$label.' را ببینید؛ قطعه مناسب را انتخاب کنید و به قیمت‌ها، فروشندگان و تعمیرگاه‌های مرتبط در پارتس‌مال برسید.');
    }

    public static function catalog(string $title, ?string $description = null): string
    {
        $title = self::plainText($title);

        if ($title === '') {
            return self::home();
        }

        return match (true) {
            self::containsAny($title, ['خودرو', 'خودروها', 'خودرو‌های']) => self::catalogCars($title),
            self::containsAny($title, ['مدل', 'مدل‌ها', 'مدل‌های']) => self::catalogModels($title),
            self::containsAny($title, ['قطعه', 'قطعات', 'لوازم یدکی']) => self::catalogParts($title),
            default => self::catalogPage($title, $description),
        };
    }

    public static function search(?string $query): string
    {
        return filled($query)
            ? 'نتایج جستجوی '.$query.' در قطعات، فروشگاه‌ها، تعمیرگاه‌ها و نمایندگی‌های پارتس‌مال.'
            : 'جستجو در قطعات، فروشگاه‌ها، تعمیرگاه‌ها، نمایندگی‌ها و کاتالوگ خودرو در پارتس‌مال.';
    }

    public static function page(string $title, ?string $content = null): string
    {
        $title = self::plainText($title);

        if (self::containsAny($title, ['درباره'])) {
            return self::limit('درباره پارتس‌مال؛ آشنایی با مرجع جستجوی لوازم یدکی خودرو و هدف ما برای دسترسی سریع‌تر خریداران به فروشگاه‌ها، تعمیرگاه‌ها و نمایندگی‌های مرتبط.');
        }

        if (self::containsAny($title, ['تماس'])) {
            return self::limit('تماس با پارتس‌مال؛ ارسال پیام، مشاهده تلفن، ایمیل و آدرس، دریافت پشتیبانی و پیگیری همکاری فروشگاه‌ها، تعمیرگاه‌ها و نمایندگی‌های قطعات خودرو.');
        }

        return self::fromText($content ?: $title.' در '.self::SITE_NAME);
    }

    public static function fromText(?string $text): string
    {
        $clean = self::plainText($text);

        if ($clean === '') {
            return self::home();
        }

        return self::limit($clean);
    }

    private static function catalogCars(string $title): string
    {
        return self::limit('مشاهده '.$title.'، انتخاب خودرو و دسترسی به مدل‌ها، قطعات، قیمت‌ها و فروشندگان مرتبط در پارتس‌مال.');
    }

    private static function listingShops(string $title): string
    {
        return self::limit('جستجو و فیلتر '.$title.'، مشاهده برندهای تحت پوشش، آدرس، امتیاز، شهر و راه‌های تماس فروشندگان لوازم یدکی در پارتس‌مال.');
    }

    private static function listingRepairShops(string $title): string
    {
        return self::limit('جستجو و فیلتر '.$title.'، مشاهده تخصص‌ها، شهر، آدرس و راه‌های تماس تعمیرکاران خودرو و مراکز خدماتی در پارتس‌مال.');
    }

    private static function listingRepresentations(string $title): string
    {
        return self::limit('جستجو و فیلتر '.$title.'، مشاهده برند خودرو، خدمات، موقعیت، شهر و راه‌های تماس نمایندگی‌های مرتبط در پارتس‌مال.');
    }

    private static function catalogModels(string $title): string
    {
        return self::limit('مشاهده '.$title.'، انتخاب مدل خودرو و رفتن به فهرست قطعات، قیمت‌ها و فروشندگان مرتبط در پارتس‌مال.');
    }

    private static function catalogParts(string $title): string
    {
        return self::limit('مشاهده '.$title.'، جستجو در قطعات خودرو و بررسی جزئیات، قیمت‌ها و فروشندگان مرتبط در پارتس‌مال.');
    }

    private static function catalogPage(string $title, ?string $description): string
    {
        $description = self::plainText($description);

        if ($description === '') {
            return self::limit('مشاهده '.$title.'، جستجو و فیلتر کاتالوگ خودرو در پارتس‌مال.');
        }

        return self::limit($title.' در پارتس‌مال؛ '.$description);
    }

    /** @param list<string> $needles */
    private static function containsAny(string $text, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($text, $needle)) {
                return true;
            }
        }

        return false;
    }

    private static function plainText(?string $text): string
    {
        return trim(preg_replace('/\s+/u', ' ', strip_tags((string) $text)) ?? '');
    }

    private static function limit(string $clean): string
    {
        return mb_strlen($clean, 'UTF-8') > 155
            ? rtrim(mb_substr($clean, 0, 152, 'UTF-8')).'...'
            : $clean;
    }
}
