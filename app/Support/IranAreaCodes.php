<?php

namespace App\Support;

final class IranAreaCodes
{
    /** @var list<string> */
    public const ALL = [
        '011', '013', '017',
        '021', '023', '024', '025', '026', '028',
        '031', '034', '035', '038',
        '041', '044', '045',
        '051', '054', '056', '058',
        '061', '066',
        '071', '074', '076', '077',
        '081', '083', '084', '086', '087',
    ];

    public static function matchPrefix(string $digits): ?string
    {
        $prefix = substr($digits, 0, 3);

        return in_array($prefix, self::ALL, true) ? $prefix : null;
    }
}
