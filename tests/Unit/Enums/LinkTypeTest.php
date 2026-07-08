<?php

namespace Tests\Unit\Enums;

use App\Enums\LinkType;
use PHPUnit\Framework\TestCase;

class LinkTypeTest extends TestCase
{
    public function test_telegram_action_url_builds_t_me_link_from_username(): void
    {
        $this->assertSame(
            'https://t.me/yadakshop',
            LinkType::Telegram->actionUrl('yadakshop'),
        );

        $this->assertSame(
            'https://t.me/yadakshop',
            LinkType::Telegram->actionUrl('@yadakshop'),
        );
    }

    public function test_telegram_action_url_preserves_full_url(): void
    {
        $this->assertSame(
            'https://t.me/yadakshop',
            LinkType::Telegram->actionUrl('https://t.me/yadakshop'),
        );
    }

    public function test_telegram_action_url_normalizes_t_me_path(): void
    {
        $this->assertSame(
            'https://t.me/yadakshop',
            LinkType::Telegram->actionUrl('t.me/yadakshop'),
        );
    }

    public function test_telegram_display_name_adds_at_prefix_for_t_me_links(): void
    {
        $this->assertSame(
            '@t.me/yadakshop',
            LinkType::Telegram->displayName('t.me/yadakshop'),
        );

        $this->assertSame(
            '@yadakshop',
            LinkType::Telegram->displayName('@yadakshop'),
        );

        $this->assertSame(
            'yadakshop',
            LinkType::Telegram->displayName('yadakshop'),
        );

        $this->assertSame(
            't.me/yadakshop',
            LinkType::Website->displayName('t.me/yadakshop'),
        );
    }

    public function test_website_action_url_does_not_use_t_me(): void
    {
        $this->assertSame(
            'https://example.com',
            LinkType::Website->actionUrl('example.com'),
        );
    }
}
