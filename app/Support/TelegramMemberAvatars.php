<?php

namespace App\Support;

class TelegramMemberAvatars
{
    /**
     * @var array<int, array{tier: string, alt: string}>
     */
    private const MEMBERS = [
        1 => ['tier' => 'good', 'alt' => 'عضو گروه'],
        2 => ['tier' => 'ok', 'alt' => 'عضو گروه'],
        3 => ['tier' => 'bad', 'alt' => 'عضو گروه'],
        4 => ['tier' => 'good', 'alt' => 'عضو گروه'],
        5 => ['tier' => 'bad', 'alt' => 'عضو گروه'],
        6 => ['tier' => 'ok', 'alt' => 'عضو گروه'],
        7 => ['tier' => 'bad', 'alt' => 'عضو گروه'],
        8 => ['tier' => 'good', 'alt' => 'عضو گروه'],
        9 => ['tier' => 'ok', 'alt' => 'عضو گروه'],
        10 => ['tier' => 'bad', 'alt' => 'عضو گروه'],
        11 => ['tier' => 'good', 'alt' => 'عضو گروه'],
        12 => ['tier' => 'ok', 'alt' => 'عضو گروه'],
        13 => ['tier' => 'bad', 'alt' => 'عضو گروه'],
        14 => ['tier' => 'good', 'alt' => 'عضو گروه'],
        15 => ['tier' => 'ok', 'alt' => 'عضو گروه'],
        16 => ['tier' => 'bad', 'alt' => 'عضو گروه'],
        17 => ['tier' => 'good', 'alt' => 'عضو گروه'],
        18 => ['tier' => 'ok', 'alt' => 'عضو گروه'],
        19 => ['tier' => 'bad', 'alt' => 'عضو گروه'],
        20 => ['tier' => 'good', 'alt' => 'عضو گروه'],
    ];

    /**
     * @return list<array{src: string, alt: string, class: string}>
     */
    public static function pickRandom(int $count = 3): array
    {
        $ids = collect(array_keys(self::MEMBERS))
            ->shuffle()
            ->take($count)
            ->values()
            ->all();

        return array_map(
            fn (int $id): array => self::resolve($id),
            $ids,
        );
    }

    /**
     * @return array{src: string, alt: string, class: string}
     */
    private static function resolve(int $id): array
    {
        $member = self::MEMBERS[$id];

        return [
            'src' => asset('img/telegram-members/member-'.str_pad((string) $id, 2, '0', STR_PAD_LEFT).'.jpg'),
            'alt' => $member['alt'],
            'class' => self::tierClass($member['tier']),
        ];
    }

    private static function tierClass(string $tier): string
    {
        return match ($tier) {
            'good' => 'object-cover object-center',
            'ok' => 'object-cover object-center contrast-[0.95] saturate-[0.9]',
            'bad' => 'object-cover object-[center_20%] contrast-[0.88] saturate-[0.8] brightness-[1.03]',
            default => 'object-cover object-center',
        };
    }
}
